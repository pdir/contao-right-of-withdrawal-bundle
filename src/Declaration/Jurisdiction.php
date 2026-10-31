<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

enum Jurisdiction: string
{
    case Germany = 'DE';
    case Austria = 'AT';

    /**
     * § 312k BGB has no counterpart in the FAGG, the cancellation button only
     * exists under German law.
     */
    public function supports(DeclarationType $type): bool
    {
        return DeclarationType::Withdrawal === $type || self::Germany === $this;
    }

    public function legalBasis(DeclarationType $type): string
    {
        return match (true) {
            DeclarationType::Cancellation === $type => '§ 312k BGB',
            self::Austria === $this => '§ 13a FAGG',
            default => '§ 356a BGB',
        };
    }
}
