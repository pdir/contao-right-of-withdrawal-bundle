<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationInput;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationValidator;
use PHPUnit\Framework\TestCase;

class DeclarationValidatorTest extends TestCase
{
    public function testRequiresOnlyTheStatutoryFieldsForWithdrawal(): void
    {
        $validator = new DeclarationValidator();

        $this->assertSame([], $validator->validate(DeclarationType::Withdrawal, new DeclarationInput(name: 'Erika', contract: 'A-1', email: 'e@example.com')));
        $this->assertSame(['name', 'contract', 'email'], array_keys($validator->validate(DeclarationType::Withdrawal, new DeclarationInput())));
    }

    public function testRejectsInvalidEmail(): void
    {
        $errors = (new DeclarationValidator())->validate(DeclarationType::Withdrawal, new DeclarationInput(name: 'Erika', contract: 'A-1', email: 'nope'));

        $this->assertArrayHasKey('email', $errors);
    }

    public function testExtraordinaryCancellationRequiresReason(): void
    {
        $validator = new DeclarationValidator();
        $base = ['name' => 'Erika', 'contract' => 'A-1', 'email' => 'e@example.com'];

        $errors = $validator->validate(DeclarationType::Cancellation, new DeclarationInput(...$base, cancellationType: 'extraordinary'));
        $this->assertArrayHasKey('cancellationReason', $errors);

        $this->assertSame([], $validator->validate(DeclarationType::Cancellation, new DeclarationInput(...$base)));
    }

    public function testRejectsInvalidTerminationDate(): void
    {
        $validator = new DeclarationValidator();
        $base = ['name' => 'Erika', 'contract' => 'A-1', 'email' => 'e@example.com'];

        $this->assertArrayHasKey('terminationDate', $validator->validate(DeclarationType::Cancellation, new DeclarationInput(...$base, terminationDate: '2026-02-31')));
        $this->assertSame([], $validator->validate(DeclarationType::Cancellation, new DeclarationInput(...$base, terminationDate: '2026-12-31')));
    }
}
