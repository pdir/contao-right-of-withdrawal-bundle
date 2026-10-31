<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Backend;

use Pdir\ContaoRightOfWithdrawalBundle\Backend\CsvExporter;
use PHPUnit\Framework\TestCase;

class CsvExporterTest extends TestCase
{
    public function testNeutralizesSpreadsheetFormulas(): void
    {
        $csv = (new CsvExporter())->export([['name' => '=HYPERLINK("http://evil")', 'contract' => '+1', 'receivedAt' => 1000]]);

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+1", $csv);
    }
}
