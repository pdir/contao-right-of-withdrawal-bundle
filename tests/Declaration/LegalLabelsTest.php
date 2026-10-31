<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\Jurisdiction;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettings;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\CreatesTranslator;
use PHPUnit\Framework\TestCase;

class LegalLabelsTest extends TestCase
{
    use CreatesTranslator;

    public function testGermanLabelsMatchTheStatutoryWording(): void
    {
        $this->assertSame('Vertrag widerrufen', $this->label(DeclarationType::Withdrawal->entryLabelKey(), 'de'));
        $this->assertSame('Widerruf bestätigen', $this->label(DeclarationType::Withdrawal->confirmLabelKey(), 'de'));
        $this->assertSame('Verträge hier kündigen', $this->label(DeclarationType::Cancellation->entryLabelKey(), 'de'));
        $this->assertSame('jetzt kündigen', $this->label(DeclarationType::Cancellation->confirmLabelKey(), 'de'));
    }

    public function testRegionalGermanKeepsTheStatutoryWording(): void
    {
        $this->assertSame('Vertrag widerrufen', $this->label(DeclarationType::Withdrawal->entryLabelKey(), 'de_AT'));
    }

    public function testEnglishLabelsMatchTheDirective(): void
    {
        $this->assertSame('Withdraw from contract here', $this->label(DeclarationType::Withdrawal->entryLabelKey(), 'en'));
        $this->assertSame('Confirm withdrawal', $this->label(DeclarationType::Withdrawal->confirmLabelKey(), 'en'));
    }

    public function testLanguagesHaveTheSameKeys(): void
    {
        $keys = fn (string $language): array => array_keys($this->createTranslator()->getCatalogue($language)->all('pdir_withdrawal'));

        $this->assertEqualsCanonicalizing($keys('de'), $keys('en'));
        $this->assertContains('error.name', $keys('en'));
    }

    public function testLegalBasisPerJurisdiction(): void
    {
        $this->assertSame('§ 356a BGB', Jurisdiction::Germany->legalBasis(DeclarationType::Withdrawal));
        $this->assertSame('§ 312k BGB', Jurisdiction::Germany->legalBasis(DeclarationType::Cancellation));
        $this->assertSame('§ 13a FAGG', Jurisdiction::Austria->legalBasis(DeclarationType::Withdrawal));
    }

    public function testCancellationIsNotOfferedInAustria(): void
    {
        $settings = new SiteSettings(1, Jurisdiction::Austria, 10, 20, '', 3, 0);

        $this->assertTrue($settings->isEnabled(DeclarationType::Withdrawal));
        $this->assertFalse($settings->isEnabled(DeclarationType::Cancellation));
    }

    private function label(string $key, string $locale): string
    {
        return $this->createTranslator()->trans($key, [], DeclarationType::TRANSLATION_DOMAIN, $locale);
    }
}
