<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class DeclarationRepository
{
    public const TABLE = 'tl_withdrawal_declaration';

    public const MAIL_PENDING = 'pending';
    public const MAIL_SENT = 'sent';
    public const MAIL_FAILED = 'failed';

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Appends the declaration to the hash chain of its website root. Concurrent
     * requests compete for the same sequence number, the unique index lets the
     * loser retry on top of the new chain end.
     *
     * @param array<string, mixed> $data
     */
    public function append(array $data): Declaration
    {
        for ($attempt = 1; ; ++$attempt) {
            $last = $this->connection->fetchAssociative(
                'SELECT sequenceNumber, hash FROM ' . self::TABLE . ' WHERE rootPage = ? ORDER BY sequenceNumber DESC LIMIT 1',
                [$data['rootPage']],
            );

            $row = $data + [
                'sequenceNumber' => (int) ($last['sequenceNumber'] ?? 0) + 1,
                'previousHash' => (string) ($last['hash'] ?? ''),
            ];

            $row['hash'] = Declaration::computeHash($row);

            try {
                $this->connection->insert(self::TABLE, $row);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= self::MAX_ATTEMPTS) {
                    throw $e;
                }

                continue;
            }

            return Declaration::fromRow(['id' => (int) $this->connection->lastInsertId()] + $row);
        }
    }

    public function find(int $id): Declaration|null
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', [$id]);

        return $row ? Declaration::fromRow($row) : null;
    }

    public function findByToken(string $token): Declaration|null
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM ' . self::TABLE . ' WHERE token = ?', [$token]);

        return $row ? Declaration::fromRow($row) : null;
    }

    /**
     * Finds an identical declaration, e.g. caused by a double click.
     *
     * @param array<string, mixed> $data
     */
    public function findRecentDuplicate(array $data, int $since): Declaration|null
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM ' . self::TABLE . ' WHERE rootPage = ? AND type = ? AND name = ? AND contract = ? AND contractPart = ? AND email = ? AND cancellationType = ? AND cancellationReason = ? AND terminationDate = ? AND receivedAt >= ? ORDER BY id DESC LIMIT 1',
            [
                $data['rootPage'],
                $data['type'],
                $data['name'],
                $data['contract'],
                $data['contractPart'],
                $data['email'],
                $data['cancellationType'],
                $data['cancellationReason'],
                $data['terminationDate'],
                $since,
            ],
        );

        return $row ? Declaration::fromRow($row) : null;
    }

    public function countByIpHash(string $ipHash, int $since): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE ipHash = ? AND receivedAt >= ?',
            [$ipHash, $since],
        );
    }

    public function updateMailStatus(int $id, string $status, int $sentAt = 0): void
    {
        $this->connection->update(self::TABLE, ['mailStatus' => $status, 'mailSentAt' => $sentAt], ['id' => $id]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findAll(): array
    {
        return $this->connection->fetchAllAssociative('SELECT * FROM ' . self::TABLE . ' ORDER BY rootPage, sequenceNumber');
    }

    public function deleteOlderThan(int $rootPage, int $timestamp): int
    {
        return (int) $this->connection->executeStatement(
            'DELETE FROM ' . self::TABLE . ' WHERE rootPage = ? AND receivedAt < ?',
            [$rootPage, $timestamp],
        );
    }

    public function clearIpHashesOlderThan(int $timestamp): void
    {
        $this->connection->executeStatement(
            'UPDATE ' . self::TABLE . " SET ipHash = '' WHERE ipHash != '' AND receivedAt < ?",
            [$timestamp],
        );
    }

    /**
     * @return list<int>
     */
    public function findRootPages(): array
    {
        return array_map('intval', $this->connection->fetchFirstColumn('SELECT DISTINCT rootPage FROM ' . self::TABLE));
    }
}
