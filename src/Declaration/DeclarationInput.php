<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

use Symfony\Component\HttpFoundation\Request;

/**
 * What the consumer entered into the form.
 */
final class DeclarationInput
{
    public const CANCELLATION_ORDINARY = 'ordinary';
    public const CANCELLATION_EXTRAORDINARY = 'extraordinary';

    public function __construct(
        public readonly string $name = '',
        public readonly string $contract = '',
        public readonly string $contractPart = '',
        public readonly string $email = '',
        public readonly string $cancellationType = self::CANCELLATION_ORDINARY,
        public readonly string $cancellationReason = '',
        public readonly string $terminationDate = '',
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $value = static fn (string $key): string => trim((string) $request->request->get($key, ''));

        return new self(
            $value('name'),
            $value('contract'),
            $value('contractPart'),
            $value('email'),
            $value('cancellationType') ?: self::CANCELLATION_ORDINARY,
            $value('cancellationReason'),
            $value('terminationDate'),
        );
    }
}
