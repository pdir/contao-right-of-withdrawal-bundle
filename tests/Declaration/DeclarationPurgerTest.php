<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationPurger;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\DatabaseTestCase;

class DeclarationPurgerTest extends DatabaseTestCase
{
    public function testKeepsDeclarationsUntilTheEndOfTheRetentionYear(): void
    {
        $this->connection->insert('tl_page', ['id' => 1, 'rowRetentionYears' => 3]);

        $repository = new DeclarationRepository($this->connection);
        $repository->append($this->row(['receivedAt' => strtotime('2026-12-31 23:00:00')]));
        $repository->append($this->row(['receivedAt' => strtotime('2027-01-01 01:00:00')]));

        $purger = new DeclarationPurger($repository, new SiteSettingsProvider($this->connection));

        $this->assertSame(0, $purger->purge(new \DateTimeImmutable('2029-12-31 12:00:00')));
        $this->assertSame(1, $purger->purge(new \DateTimeImmutable('2030-01-01 12:00:00')));
        $this->assertSame(1, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_withdrawal_declaration'));
    }

    public function testClearsOldIpHashes(): void
    {
        $this->connection->insert('tl_page', ['id' => 1]);

        $repository = new DeclarationRepository($this->connection);
        $now = new \DateTimeImmutable('2026-10-30');

        $repository->append($this->row(['ipHash' => 'old', 'receivedAt' => $now->modify('-91 days')->getTimestamp()]));
        $repository->append($this->row(['ipHash' => 'new', 'receivedAt' => $now->modify('-89 days')->getTimestamp()]));

        (new DeclarationPurger($repository, new SiteSettingsProvider($this->connection)))->purge($now);

        $this->assertSame(['', 'new'], $this->connection->fetchFirstColumn('SELECT ipHash FROM tl_withdrawal_declaration ORDER BY id'));
    }
}
