<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Declaration;

use Pdir\ContaoRightOfWithdrawalBundle\Declaration\ChainVerifier;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\DatabaseTestCase;

class HashChainTest extends DatabaseTestCase
{
    public function testLinksEntriesPerWebsiteRoot(): void
    {
        $repository = new DeclarationRepository($this->connection);

        $first = $repository->append($this->row());
        $second = $repository->append($this->row());
        $otherRoot = $repository->append($this->row(['rootPage' => 2]));

        $this->assertSame(1, $first->sequenceNumber);
        $this->assertSame('', $first->previousHash);
        $this->assertSame(2, $second->sequenceNumber);
        $this->assertSame($first->hash, $second->previousHash);
        $this->assertSame(1, $otherRoot->sequenceNumber);
        $this->assertSame([], (new ChainVerifier($repository))->verify());
    }

    public function testDetectsChangedContent(): void
    {
        $repository = new DeclarationRepository($this->connection);
        $declaration = $repository->append($this->row());

        $this->connection->update('tl_withdrawal_declaration', ['contract' => 'B-999'], ['id' => $declaration->id]);

        $this->assertStringContainsString('checksum', (new ChainVerifier($repository))->verify()[0]);
    }

    public function testDetectsRemovedEntry(): void
    {
        $repository = new DeclarationRepository($this->connection);
        $repository->append($this->row());
        $middle = $repository->append($this->row());
        $repository->append($this->row());

        $this->connection->delete('tl_withdrawal_declaration', ['id' => $middle->id]);

        $this->assertStringContainsString('missing', (new ChainVerifier($repository))->verify()[0]);
    }

    public function testProcessingDataDoesNotBreakTheChain(): void
    {
        $repository = new DeclarationRepository($this->connection);
        $declaration = $repository->append($this->row());

        $this->connection->update('tl_withdrawal_declaration', ['status' => 'done', 'notes' => 'Erstattet', 'mailStatus' => 'sent'], ['id' => $declaration->id]);

        $this->assertSame([], (new ChainVerifier($repository))->verify());
    }

    public function testChainMayStartLaterAfterRetention(): void
    {
        $repository = new DeclarationRepository($this->connection);
        $first = $repository->append($this->row());
        $repository->append($this->row());

        $this->connection->delete('tl_withdrawal_declaration', ['id' => $first->id]);

        $this->assertSame([], (new ChainVerifier($repository))->verify());
    }
}
