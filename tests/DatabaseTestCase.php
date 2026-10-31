<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

/**
 * Creates the table from the real DCA, so the tests also cover the field definitions.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);

        $dca = $this->loadDca();
        $columns = [];

        foreach ($dca['fields'] as $name => $field) {
            $sql = str_replace(['int(10) unsigned NOT NULL auto_increment', 'int(10) unsigned'], ['INTEGER PRIMARY KEY AUTOINCREMENT', 'INTEGER'], $field['sql']);
            $columns[] = $name . ' ' . $sql;
        }

        $this->connection->executeStatement('CREATE TABLE tl_withdrawal_declaration (' . implode(', ', $columns) . ')');

        foreach ($dca['config']['sql']['keys'] as $key => $type) {
            if ('unique' === $type) {
                $this->connection->executeStatement(\sprintf('CREATE UNIQUE INDEX uniq_%s ON tl_withdrawal_declaration (%s)', md5($key), $key));
            }
        }

        $this->connection->executeStatement("CREATE TABLE tl_page (id INTEGER PRIMARY KEY, type TEXT DEFAULT 'root', sorting INTEGER DEFAULT 0, adminEmail TEXT DEFAULT '', language TEXT DEFAULT 'de', rowJurisdiction TEXT DEFAULT 'DE', rowWithdrawalPage INTEGER DEFAULT 0, rowCancellationPage INTEGER DEFAULT 0, rowRecipient TEXT DEFAULT '', rowRetentionYears INTEGER DEFAULT 3, rowNotification INTEGER DEFAULT 0)");
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadDca(): array
    {
        require __DIR__ . '/../contao/dca/tl_withdrawal_declaration.php';

        return $GLOBALS['TL_DCA']['tl_withdrawal_declaration'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function row(array $overrides = []): array
    {
        static $counter = 0;

        return $overrides + [
            'tstamp' => 1000,
            'rootPage' => 1,
            'type' => 'withdrawal',
            'jurisdiction' => 'DE',
            'language' => 'de',
            'name' => 'Erika Mustermann',
            'contract' => 'A-100',
            'contractPart' => '',
            'email' => 'erika@example.com',
            'cancellationType' => '',
            'cancellationReason' => '',
            'terminationDate' => '',
            'receivedAt' => 1000,
            'token' => str_pad((string) ++$counter, 64, '0', STR_PAD_LEFT),
            'ipHash' => '',
            'mailStatus' => 'pending',
            'mailSentAt' => 0,
            'status' => 'received',
            'notes' => '',
            'orderId' => 0,
            'orderMatch' => '',
        ];
    }
}
