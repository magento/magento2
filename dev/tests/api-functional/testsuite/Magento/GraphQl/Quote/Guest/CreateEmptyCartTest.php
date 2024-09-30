<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Test for empty cart creation mutation
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var string
     */
    private $maskedQuoteId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    public function testCreateEmptyCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
        self::assertEquals('1', $guestCart->getCustomerIsGuest());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = $this->getQuery();
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlMutation($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertSame('fixture_second_store', $guestCart->getStore()->getCode());
        self::assertEquals('1', $guestCart->getCustomerIsGuest());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartWithPredefinedCartId()
    {
        $predefinedCartId = '572cda51902b5b517c0e1a2b2fd004b4';

        $query = <<<QUERY
mutation {
  createEmptyCart (input: {cart_id: "{$predefinedCartId}"})
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertEquals($predefinedCartId, $response['createEmptyCart']);

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testCreateEmptyCartIfPredefinedCartIdAlreadyExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart with ID "572cda51902b5b517c0e1a2b2fd004b4" already exists.');

        $predefinedCartId = '572cda51902b5b517c0e1a2b2fd004b4';

        $query = <<<QUERY
mutation {
  createEmptyCart (input: {cart_id: "{$predefinedCartId}"})
}
QUERY;
        $this->graphQlMutation($query);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testCreateEmptyCartWithWrongPredefinedCartId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart ID length should to be 32 symbols.');

        $predefinedCartId = '572';

        $query = <<<QUERY
mutation {
  createEmptyCart (input: {cart_id: "{$predefinedCartId}"})
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
    }

    protected function tearDown(): void
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        foreach ($quoteCollection as $quote) {
            $this->quoteResource->delete($quote);

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quote->getId())
                ->delete();
        }
        parent::tearDown();
    }
}
