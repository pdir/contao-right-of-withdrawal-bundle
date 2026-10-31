<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Isotope\OrderMatcher;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Stores a declaration first and sends the receipt afterwards: the declaration
 * is valid from the moment the consumer pressed the button, regardless of
 * whether the mail server is available.
 */
class DeclarationSubmitter
{
    public const MAX_PER_HOUR = 20;
    private const DUPLICATE_WINDOW = 60;

    public function __construct(
        private readonly DeclarationRepository $repository,
        private readonly DeclarationNotifier $notifier,
        private readonly OrderMatcher $orderMatcher,
        private readonly string $secret,
    ) {
    }

    public function isRateLimited(string|null $clientIp): bool
    {
        if (null === $clientIp) {
            return false;
        }

        return $this->repository->countByIpHash($this->hashIp($clientIp), time() - 3600) >= self::MAX_PER_HOUR;
    }

    public function submit(DeclarationType $type, DeclarationInput $input, SiteSettings $settings, string|null $clientIp): Declaration
    {
        $now = time();

        $data = [
            'tstamp' => $now,
            'rootPage' => $settings->rootPage,
            'type' => $type->value,
            'jurisdiction' => $settings->jurisdiction->value,
            'language' => $settings->language,
            'name' => $input->name,
            'contract' => $input->contract,
            'contractPart' => $input->contractPart,
            'email' => $input->email,
            'cancellationType' => DeclarationType::Cancellation === $type ? $input->cancellationType : '',
            'cancellationReason' => DeclarationType::Cancellation === $type ? $input->cancellationReason : '',
            'terminationDate' => DeclarationType::Cancellation === $type ? $input->terminationDate : '',
            'receivedAt' => $now,
            'token' => bin2hex(random_bytes(32)),
            'ipHash' => null === $clientIp ? '' : $this->hashIp($clientIp),
            'mailStatus' => DeclarationRepository::MAIL_PENDING,
            'mailSentAt' => 0,
            'status' => 'received',
            'notes' => '',
        ];

        if ($duplicate = $this->repository->findRecentDuplicate($data, $now - self::DUPLICATE_WINDOW)) {
            return $duplicate;
        }

        $match = $this->orderMatcher->match($input->contract, $input->email);

        $declaration = $this->repository->append($data + ['orderId' => $match['orderId'], 'orderMatch' => $match['match']]);

        $this->notifier->send($declaration, $settings);

        return $this->repository->find($declaration->id) ?? $declaration;
    }

    private function hashIp(string $clientIp): string
    {
        return hash_hmac('sha256', IpUtils::anonymize($clientIp), $this->secret);
    }
}
