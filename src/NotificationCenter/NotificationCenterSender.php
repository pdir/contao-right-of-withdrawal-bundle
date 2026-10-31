<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\NotificationCenter;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\Declaration;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettings;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class NotificationCenterSender
{
    public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function send(Declaration $declaration, SiteSettings $settings): bool
    {
        return $this->notificationCenter->sendNotification($settings->notification, [
            'email' => $declaration->email,
            'recipient' => $settings->recipient,
            'number' => (string) $declaration->sequenceNumber,
            'type' => $this->translator->trans($declaration->type->declarationLabelKey(), [], DeclarationType::TRANSLATION_DOMAIN, $declaration->language),
            'legal_basis' => $declaration->jurisdiction->legalBasis($declaration->type),
            'name' => $declaration->name,
            'contract' => $declaration->contract,
            'contract_part' => $declaration->contractPart,
            'cancellation_type' => $declaration->cancellationType,
            'cancellation_reason' => $declaration->cancellationReason,
            'termination_date' => $declaration->terminationDate,
            'received_at' => $declaration->receivedAtFormatted(),
            'hash' => $declaration->hash,
        ])->wereAllDelivered();
    }
}
