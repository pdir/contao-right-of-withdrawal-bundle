<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

/**
 * The German button labels are the words named by §§ 356a, 312k BGB and § 13a FAGG.
 * They live in the translation files and are protected by LegalLabelsTest.
 */
enum DeclarationType: string
{
    case Withdrawal = 'withdrawal';
    case Cancellation = 'cancellation';

    public const TRANSLATION_DOMAIN = 'pdir_withdrawal';

    public function entryLabelKey(): string
    {
        return $this->value . '.entry';
    }

    public function confirmLabelKey(): string
    {
        return $this->value . '.confirm';
    }

    public function declarationLabelKey(): string
    {
        return $this->value . '.declaration';
    }
}
