<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Tests\Isotope;

use Pdir\ContaoRightOfWithdrawalBundle\Isotope\OrderMatcher;
use Pdir\ContaoRightOfWithdrawalBundle\Tests\DatabaseTestCase;

class OrderMatcherTest extends DatabaseTestCase
{
    public function testDoesNothingWithoutIsotope(): void
    {
        $this->assertSame(['orderId' => 0, 'match' => ''], (new OrderMatcher($this->connection))->match('A-1', 'e@example.com'));
    }

    public function testMatchesOrders(): void
    {
        $this->connection->executeStatement('CREATE TABLE tl_iso_address (id INTEGER PRIMARY KEY, email TEXT)');
        $this->connection->executeStatement('CREATE TABLE tl_iso_product_collection (id INTEGER PRIMARY KEY, type TEXT, document_number TEXT, billing_address_id INTEGER)');
        $this->connection->insert('tl_iso_address', ['id' => 5, 'email' => 'Erika@Example.com']);
        $this->connection->insert('tl_iso_product_collection', ['id' => 9, 'type' => 'order', 'document_number' => 'A-1', 'billing_address_id' => 5]);
        $this->connection->insert('tl_iso_product_collection', ['id' => 10, 'type' => 'cart', 'document_number' => 'C-1', 'billing_address_id' => 5]);

        $matcher = new OrderMatcher($this->connection);

        $this->assertSame(['orderId' => 9, 'match' => OrderMatcher::MATCHED], $matcher->match('A-1', 'erika@example.com'));
        $this->assertSame(['orderId' => 9, 'match' => OrderMatcher::EMAIL_MISMATCH], $matcher->match('A-1', 'other@example.com'));
        $this->assertSame(['orderId' => 0, 'match' => OrderMatcher::NOT_FOUND], $matcher->match('C-1', 'erika@example.com'));
    }
}
