<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter\NotificationCenterSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Sends the receipt to the consumer and a copy to the operator. A failure is
 * recorded and shown in the back end so the receipt can be sent again.
 */
class DeclarationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly DeclarationRepository $repository,
        private readonly TranslatorInterface $translator,
        private readonly LocaleSwitcher $localeSwitcher,
        private readonly LoggerInterface|null $logger = null,
        private readonly NotificationCenterSender|null $notificationCenter = null,
    ) {
    }

    public function send(Declaration $declaration, SiteSettings $settings): bool
    {
        try {
            if ($settings->notification > 0 && null !== $this->notificationCenter) {
                $delivered = $this->notificationCenter->send($declaration, $settings);
            } else {
                // The language of the declaration, also when resent from the back end
                $this->localeSwitcher->runWithLocale($declaration->language, fn () => $this->sendMails($declaration, $settings));
                $delivered = true;
            }
        } catch (\Throwable $e) {
            $this->logger?->error('Could not send the receipt of declaration {id}: {message}', [
                'id' => $declaration->id,
                'message' => $e->getMessage(),
            ]);

            $delivered = false;
        }

        $this->repository->updateMailStatus(
            $declaration->id,
            $delivered ? DeclarationRepository::MAIL_SENT : DeclarationRepository::MAIL_FAILED,
            $delivered ? time() : 0,
        );

        return $delivered;
    }

    private function sendMails(Declaration $declaration, SiteSettings $settings): void
    {
        $context = ['declaration' => $declaration, 'settings' => $settings];
        $subject = fn (string $key): string => $this->translator->trans($key, [
            '%label%' => $this->translator->trans($declaration->type->declarationLabelKey(), [], DeclarationType::TRANSLATION_DOMAIN),
            '%number%' => $declaration->sequenceNumber,
        ], DeclarationType::TRANSLATION_DOMAIN);

        // Without a recipient the sender stays empty and the default sender of
        // the mailer configuration applies. If there is none, sending fails and
        // the failure shows up in the back end.
        $receipt = (new Email())
            ->to(new Address($declaration->email, $declaration->name))
            ->subject($subject('mail.receipt_subject'))
            ->text($this->twig->render('@PdirContaoRightOfWithdrawal/mail/receipt.txt.twig', $context))
        ;

        if ('' !== $settings->recipient) {
            $receipt->from($settings->recipient)->replyTo($settings->recipient);
        }

        $this->mailer->send($receipt);

        if ('' === $settings->recipient) {
            return;
        }

        $this->mailer->send(
            (new Email())
                ->from($settings->recipient)
                ->to($settings->recipient)
                ->replyTo($declaration->email)
                ->subject($subject('mail.operator_subject'))
                ->text($this->twig->render('@PdirContaoRightOfWithdrawal/mail/operator.txt.twig', $context))
        );
    }
}
