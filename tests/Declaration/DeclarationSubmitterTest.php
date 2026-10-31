<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\ChainVerifier;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationInput;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationNotifier;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationSubmitter;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\Jurisdiction;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettings;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Pdir\ContaoRightOfWithdrawalBundle\Isotope\OrderMatcher;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\CreatesTranslator;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\DatabaseTestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DeclarationSubmitterTest extends DatabaseTestCase
{
    use CreatesTranslator;

    /** @var list<Email> */
    private array $sent = [];
    private bool $failing = false;

    public function testSendsReceiptWithStatutoryContentAndOperatorCopy(): void
    {
        $declaration = $this->submitter()->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), '203.0.113.5');

        $this->assertSame('sent', $declaration->mailStatus);
        $this->assertCount(2, $this->sent);

        $receipt = $this->sent[0];
        $this->assertSame('erika@example.com', $receipt->getTo()[0]->getAddress());
        $this->assertSame('shop@example.com', $receipt->getFrom()[0]->getAddress());

        $body = $receipt->getTextBody();
        $this->assertStringContainsString('Erika Mustermann', $body);
        $this->assertStringContainsString('A-100', $body);
        $this->assertStringContainsString('§ 356a BGB', $body);
        $this->assertStringContainsString($declaration->receivedAtFormatted(), $body);
        $this->assertMatchesRegularExpression('/\d{2}\.\d{2}\.\d{4}, \d{2}:\d{2}:\d{2}/', $body);

        $this->assertSame('shop@example.com', $this->sent[1]->getTo()[0]->getAddress());
    }

    public function testDoesNotEscapeUserInputInPlainTextMails(): void
    {
        $this->submitter()->submit(DeclarationType::Withdrawal, new DeclarationInput(name: "Conan O'Brien", contract: 'A&B', email: 'c@example.com'), $this->settings(), null);

        $this->assertStringContainsString("Conan O'Brien", $this->sent[0]->getTextBody());
        $this->assertStringContainsString('A&B', $this->sent[0]->getTextBody());
    }

    public function testKeepsDeclarationWhenMailFails(): void
    {
        $this->failing = true;

        $declaration = $this->submitter()->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), null);

        $this->assertSame('failed', $declaration->mailStatus);
        $this->assertSame(1, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM tl_withdrawal_declaration'));
    }

    public function testDoubleSubmitCreatesOneDeclaration(): void
    {
        $submitter = $this->submitter();

        $first = $submitter->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), null);
        $second = $submitter->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), null);

        $this->assertSame($first->id, $second->id);
        $this->assertCount(2, $this->sent);
    }

    public function testRateLimitsPerAnonymizedAddress(): void
    {
        $submitter = $this->submitter();
        $this->assertFalse($submitter->isRateLimited('203.0.113.5'));

        for ($i = 0; $i < DeclarationSubmitter::MAX_PER_HOUR; ++$i) {
            $submitter->submit(DeclarationType::Withdrawal, new DeclarationInput(name: 'Erika', contract: 'A-' . $i, email: 'e@example.com'), $this->settings(), '203.0.113.5');
        }

        $this->assertTrue($submitter->isRateLimited('203.0.113.77'));
        $this->assertFalse($submitter->isRateLimited('198.51.100.1'));
        $this->assertFalse($submitter->isRateLimited(null));
    }

    public function testStoresNoPlainIpAddress(): void
    {
        $this->submitter()->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), '203.0.113.5');

        $this->assertStringNotContainsString('203.0.113', (string) $this->connection->fetchOne('SELECT ipHash FROM tl_withdrawal_declaration'));
    }

    public function testCancellationStoresItsFields(): void
    {
        $input = new DeclarationInput(name: 'Erika', contract: 'V-1', email: 'e@example.com', cancellationType: 'extraordinary', cancellationReason: 'Umzug', terminationDate: '2026-12-31');

        $declaration = $this->submitter()->submit(DeclarationType::Cancellation, $input, $this->settings(), null);

        $this->assertSame('extraordinary', $declaration->cancellationType);
        $this->assertStringContainsString('außerordentlich', $this->sent[0]->getTextBody());
        $this->assertStringContainsString('Umzug', $this->sent[0]->getTextBody());
        $this->assertStringContainsString('§ 312k BGB', $this->sent[0]->getTextBody());
    }

    public function testUsesTheLanguageOfTheWebsiteRoot(): void
    {
        $settings = new SiteSettings(1, Jurisdiction::Germany, 10, 20, 'shop@example.com', 3, 0, 'en');

        $declaration = $this->submitter()->submit(DeclarationType::Cancellation, $this->input(), $settings, null);

        $this->assertSame('en', $declaration->language);
        $this->assertSame('Confirmation of receipt: Cancellation no. 1', $this->sent[0]->getSubject());
        $this->assertStringContainsString('Hello Erika Mustermann,', $this->sent[0]->getTextBody());
        $this->assertStringContainsString('Terminate on: the earliest possible date', $this->sent[0]->getTextBody());
        $this->assertStringContainsString('§ 312k BGB', $this->sent[0]->getTextBody());
        $this->assertSame('New declaration: Cancellation no. 1', $this->sent[1]->getSubject());
        $this->assertStringContainsString('Checksum: ', $this->sent[1]->getTextBody());
    }

    public function testResendKeepsTheLanguageOfTheDeclaration(): void
    {
        $settings = new SiteSettings(1, Jurisdiction::Germany, 10, 20, '', 3, 0, 'en');
        $repository = new DeclarationRepository($this->connection);

        $declaration = $this->submitter()->submit(DeclarationType::Withdrawal, $this->input(), $settings, null);
        $this->sent = [];

        // The back end user works in German
        $this->notifier($repository)->send($repository->find($declaration->id), $settings);

        $this->assertStringContainsString('Hello', $this->sent[0]->getTextBody());
    }

    public function testLanguageIsCoveredByTheChecksum(): void
    {
        $repository = new DeclarationRepository($this->connection);
        $declaration = $this->submitter()->submit(DeclarationType::Withdrawal, $this->input(), $this->settings(), null);

        $this->connection->update('tl_withdrawal_declaration', ['language' => 'en'], ['id' => $declaration->id]);

        $this->assertStringContainsString('checksum', (new ChainVerifier($repository))->verify()[0]);
    }

    public function testSiteSettingsProvideTheLanguageAsLocale(): void
    {
        $this->connection->insert('tl_page', ['id' => 1, 'language' => 'de-CH']);

        $this->assertSame('de_CH', (new SiteSettingsProvider($this->connection))->get(1)->language);
    }

    private function input(): DeclarationInput
    {
        return new DeclarationInput(name: 'Erika Mustermann', contract: 'A-100', email: 'erika@example.com');
    }

    private function settings(): SiteSettings
    {
        return new SiteSettings(1, Jurisdiction::Germany, 10, 20, 'shop@example.com', 3, 0);
    }

    private function submitter(): DeclarationSubmitter
    {
        $repository = new DeclarationRepository($this->connection);

        return new DeclarationSubmitter(
            $repository,
            $this->notifier($repository),
            new OrderMatcher($this->connection),
            'secret',
        );
    }

    private function notifier(DeclarationRepository $repository): DeclarationNotifier
    {
        $mailer = new class($this) implements MailerInterface {
            public function __construct(private readonly DeclarationSubmitterTest $test)
            {
            }

            public function send(RawMessage $message, Envelope|null $envelope = null): void
            {
                $this->test->recordMail($message);
            }
        };

        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../templates', 'PdirContaoRightOfWithdrawal');

        $translator = $this->createTranslator();

        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new TranslationExtension($translator));

        return new DeclarationNotifier($mailer, $twig, $repository, $translator, new LocaleSwitcher('de', [$translator]));
    }

    public function recordMail(RawMessage $message): void
    {
        if ($this->failing) {
            throw new TransportException('Connection refused');
        }

        $this->sent[] = $message;
    }
}
