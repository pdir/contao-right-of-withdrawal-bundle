<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Declaration;

use Doctrine\DBAL\Connection;

class SiteSettingsProvider
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function get(int $rootPage): SiteSettings
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM tl_page WHERE id = ?', [$rootPage]) ?: [];

        return new SiteSettings(
            $rootPage,
            Jurisdiction::tryFrom((string) ($row['rowJurisdiction'] ?? '')) ?? Jurisdiction::Germany,
            (int) ($row['rowWithdrawalPage'] ?? 0),
            (int) ($row['rowCancellationPage'] ?? 0),
            (string) (($row['rowRecipient'] ?? '') ?: ($row['adminEmail'] ?? '')),
            (int) (($row['rowRetentionYears'] ?? 0) ?: SiteSettings::DEFAULT_RETENTION_YEARS),
            (int) ($row['rowNotification'] ?? 0),
            str_replace('-', '_', (string) (($row['language'] ?? '') ?: 'de')),
        );
    }

    /**
     * @return list<SiteSettings>
     */
    public function all(): array
    {
        $ids = $this->connection->fetchFirstColumn("SELECT id FROM tl_page WHERE type = 'root' ORDER BY sorting");

        return array_map(fn ($id) => $this->get((int) $id), $ids);
    }
}
