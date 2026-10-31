<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Isotope;

use Doctrine\DBAL\Connection;

/**
 * Assigns a declaration to an Isotope order, if Isotope is installed. The result
 * is a hint for the operator and never prevents a declaration.
 */
class OrderMatcher
{
    public const NONE = '';
    public const MATCHED = 'matched';
    public const EMAIL_MISMATCH = 'email_mismatch';
    public const NOT_FOUND = 'not_found';

    private bool|null $available = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array{orderId: int, match: string}
     */
    public function match(string $contract, string $email): array
    {
        if (!$this->isAvailable()) {
            return ['orderId' => 0, 'match' => self::NONE];
        }

        $order = $this->connection->fetchAssociative(
            "SELECT o.id, a.email FROM tl_iso_product_collection o LEFT JOIN tl_iso_address a ON a.id = o.billing_address_id WHERE o.type = 'order' AND o.document_number = ?",
            [$contract],
        );

        if (!$order) {
            return ['orderId' => 0, 'match' => self::NOT_FOUND];
        }

        return [
            'orderId' => (int) $order['id'],
            'match' => 0 === strcasecmp((string) $order['email'], $email) ? self::MATCHED : self::EMAIL_MISMATCH,
        ];
    }

    private function isAvailable(): bool
    {
        return $this->available ??= $this->connection->createSchemaManager()->tablesExist(['tl_iso_product_collection', 'tl_iso_address']);
    }
}
