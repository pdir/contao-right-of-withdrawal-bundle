<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

/**
 * A stored declaration.
 */
final class Declaration
{
    public function __construct(
        public readonly int $id,
        public readonly int $rootPage,
        public readonly int $sequenceNumber,
        public readonly DeclarationType $type,
        public readonly Jurisdiction $jurisdiction,
        public readonly string $name,
        public readonly string $contract,
        public readonly string $contractPart,
        public readonly string $email,
        public readonly string $cancellationType,
        public readonly string $cancellationReason,
        public readonly string $terminationDate,
        public readonly int $receivedAt,
        public readonly string $previousHash,
        public readonly string $hash,
        public readonly string $token,
        public readonly string $mailStatus,
        public readonly int $orderId,
        public readonly string $orderMatch,
        public readonly string $language,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (int) $row['rootPage'],
            (int) $row['sequenceNumber'],
            DeclarationType::from($row['type']),
            Jurisdiction::from($row['jurisdiction']),
            (string) $row['name'],
            (string) $row['contract'],
            (string) $row['contractPart'],
            (string) $row['email'],
            (string) $row['cancellationType'],
            (string) $row['cancellationReason'],
            (string) $row['terminationDate'],
            (int) $row['receivedAt'],
            (string) $row['previousHash'],
            (string) $row['hash'],
            (string) $row['token'],
            (string) $row['mailStatus'],
            (int) $row['orderId'],
            (string) $row['orderMatch'],
            (string) $row['language'],
        );
    }

    public function receivedAtFormatted(): string
    {
        return (new \DateTimeImmutable('@' . $this->receivedAt))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
            ->format('d.m.Y, H:i:s T')
        ;
    }

    /**
     * The hash covers everything the declaration says. The processing data
     * (status, notes, mail status) may change and is therefore left out.
     *
     * @param array<string, mixed> $row
     */
    public static function computeHash(array $row): string
    {
        return hash('sha256', json_encode([
            (int) $row['rootPage'],
            (int) $row['sequenceNumber'],
            (string) $row['type'],
            (string) $row['jurisdiction'],
            (string) $row['name'],
            (string) $row['contract'],
            (string) $row['contractPart'],
            (string) $row['email'],
            (string) $row['cancellationType'],
            (string) $row['cancellationReason'],
            (string) $row['terminationDate'],
            (int) $row['receivedAt'],
            (string) $row['previousHash'],
            (string) $row['language'],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }
}
