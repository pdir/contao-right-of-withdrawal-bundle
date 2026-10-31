<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Template;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\Declaration;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationInput;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\CreatesTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class WithdrawalFormTemplateTest extends TestCase
{
    use CreatesTranslator;

    public function testRendersWithdrawalFormWithoutReasonField(): void
    {
        $html = $this->render(['function' => 'withdrawal', 'confirm_label' => 'Widerruf bestätigen', 'input' => new DeclarationInput(contract: '"><script>'), 'errors' => [], 'form_id' => 'f1']);

        $this->assertStringContainsString('>Widerruf bestätigen</button>', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="contractPart"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringNotContainsString('cancellationReason', $html);
        $this->assertStringNotContainsString('"><script>', $html);
    }

    public function testRendersStatutoryCancellationFields(): void
    {
        $html = $this->render(['function' => 'cancellation', 'confirm_label' => 'jetzt kündigen', 'input' => new DeclarationInput(), 'errors' => ['name' => 'Fehler'], 'form_id' => 'f1']);

        $this->assertStringContainsString('>jetzt kündigen</button>', $html);
        $this->assertStringContainsString('name="cancellationType"', $html);
        $this->assertStringContainsString('name="cancellationReason"', $html);
        $this->assertStringContainsString('name="terminationDate"', $html);
        $this->assertStringContainsString('aria-describedby="error_name"', $html);
    }

    public function testRendersReceipt(): void
    {
        $declaration = Declaration::fromRow(['id' => 1, 'rootPage' => 1, 'sequenceNumber' => 7, 'type' => 'withdrawal', 'jurisdiction' => 'DE', 'name' => 'Erika', 'contract' => 'A-1', 'contractPart' => '', 'email' => 'e@example.com', 'cancellationType' => '', 'cancellationReason' => '', 'terminationDate' => '', 'receivedAt' => 1000, 'previousHash' => '', 'hash' => 'h', 'token' => 't', 'mailStatus' => 'failed', 'orderId' => 0, 'orderMatch' => '', 'language' => 'de']);

        $html = $this->render(['function' => 'withdrawal', 'confirm_label' => 'Widerruf bestätigen', 'declaration' => $declaration, 'receipt_token' => 't']);

        $this->assertStringContainsString('Widerruf eingegangen', $html);
        $this->assertStringContainsString('/receipt/t', $html);
        $this->assertStringContainsString('nicht per E-Mail versendet', $html);
    }

    public function testRendersFormInEnglish(): void
    {
        $html = $this->render(['function' => 'cancellation', 'confirm_label' => 'Cancel now', 'input' => new DeclarationInput(), 'errors' => [], 'form_id' => 'f1'], 'en');

        $this->assertStringContainsString('Contract (e.g. contract or customer number)', $html);
        $this->assertStringContainsString('(optional)', $html);
        $this->assertStringContainsString('> extraordinary</label>', $html);
        $this->assertStringNotContainsString('Kündigung', $html);
    }

    public function testDownloadedReceiptEscapesUserInput(): void
    {
        $declaration = Declaration::fromRow(['id' => 1, 'rootPage' => 1, 'sequenceNumber' => 7, 'type' => 'withdrawal', 'jurisdiction' => 'DE', 'name' => '<script>alert(1)</script>', 'contract' => 'A-1', 'contractPart' => '', 'email' => 'e@example.com', 'cancellationType' => '', 'cancellationReason' => '', 'terminationDate' => '', 'receivedAt' => 1000, 'previousHash' => '', 'hash' => 'h', 'token' => 't', 'mailStatus' => 'sent', 'orderId' => 0, 'orderMatch' => '', 'language' => 'de']);

        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../../templates', 'PdirContaoRightOfWithdrawal');

        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new TranslationExtension($this->createTranslator()));

        $html = $twig->render('@PdirContaoRightOfWithdrawal/receipt/receipt.html.twig', ['declaration' => $declaration]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Widerruf bestätigen', $html);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function render(array $context, string $locale = 'de'): string
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . '/../Fixtures/templates', 'Contao');
        $loader->addPath(__DIR__ . '/../../contao/templates', 'Contao');

        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new TranslationExtension($this->createTranslator($locale)));
        $twig->addGlobal('contao', ['request_token' => 'token']);
        $twig->addFunction(new TwigFunction('path', static fn (string $route, array $parameters): string => '/receipt/' . $parameters['token']));

        return $twig->render('@Contao/content_element/withdrawal_form.html.twig', $context);
    }
}
