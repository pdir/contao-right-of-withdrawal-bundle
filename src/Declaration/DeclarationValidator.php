<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

/**
 * Only checks what the law requires. It must never check whether the contract
 * exists or the period is still running: a declaration that cannot be sent is
 * worse than one the operator has to reject afterwards.
 */
class DeclarationValidator
{
    private const MAX_LENGTH = 255;

    /**
     * @return array<string, string> The translation keys of the errors by field name
     */
    public function validate(DeclarationType $type, DeclarationInput $input): array
    {
        $errors = [];

        if ('' === $input->name) {
            $errors['name'] = 'error.name';
        }

        if ('' === $input->contract) {
            $errors['contract'] = 'error.contract';
        }

        if (false === filter_var($input->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'error.email';
        }

        if (DeclarationType::Cancellation === $type) {
            if (!\in_array($input->cancellationType, [DeclarationInput::CANCELLATION_ORDINARY, DeclarationInput::CANCELLATION_EXTRAORDINARY], true)) {
                $errors['cancellationType'] = 'error.cancellation_type';
            }

            if (DeclarationInput::CANCELLATION_EXTRAORDINARY === $input->cancellationType && '' === $input->cancellationReason) {
                $errors['cancellationReason'] = 'error.cancellation_reason';
            }

            if ('' !== $input->terminationDate && !$this->isDate($input->terminationDate)) {
                $errors['terminationDate'] = 'error.termination_date';
            }
        }

        foreach (['name', 'contract', 'email'] as $field) {
            if (!isset($errors[$field]) && mb_strlen($input->$field) > self::MAX_LENGTH) {
                $errors[$field] = 'error.too_long';
            }
        }

        return $errors;
    }

    private function isDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return false !== $date && $date->format('Y-m-d') === $value;
    }
}
