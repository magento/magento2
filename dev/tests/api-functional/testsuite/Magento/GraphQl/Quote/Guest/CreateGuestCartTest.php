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
 * Test for guest cart creation mutation
 */
class CreateGuestCartTest extends GraphQlAbstract
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

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    public function testSuccessfulCreateGuestCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createGuestCart', $response);
        self::assertNotEmpty($response['createGuestCart']);
        self::assertArrayHasKey('cart', $response['createGuestCart']);
        self::assertNotEmpty($response['createGuestCart']['cart']);
        self::assertArrayHasKey('id', $response['createGuestCart']['cart']);
        self::assertNotEmpty($response['createGuestCart']['cart']['id']);

        $guestCart = $this->guestCartRepository->get($response['createGuestCart']['cart']['id']);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
        self::assertEquals('1', $guestCart->getCustomerIsGuest());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testSuccessfulWithNotDefaultStore()
    {
        $query = $this->getQuery();
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlMutation($query, [], '', $headerMap);

        self::assertArrayHasKey('createGuestCart', $response);
        self::assertNotEmpty($response['createGuestCart']);
        self::assertArrayHasKey('cart', $response['createGuestCart']);
        self::assertNotEmpty($response['createGuestCart']['cart']);
        self::assertArrayHasKey('id', $response['createGuestCart']['cart']);
        self::assertNotEmpty($response['createGuestCart']['cart']['id']);

        $guestCart = $this->guestCartRepository->get($response['createGuestCart']['cart']['id']);

        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
        self::assertSame('fixture_second_store', $guestCart->getStore()->getCode());
        self::assertEquals('1', $guestCart->getCustomerIsGuest());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testSuccessfulWithPredefinedCartId()
    {
        $predefinedCartId = '572cda51902b5b517c0e1a2b2fd004b4';

        $query = $this->getQueryWithCartId($predefinedCartId);
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('createGuestCart', $response);
        self::assertNotEmpty($response['createGuestCart']);
        self::assertArrayHasKey('cart', $response['createGuestCart']);
        self::assertNotEmpty($response['createGuestCart']['cart']);
        self::assertArrayHasKey('id', $response['createGuestCart']['cart']);
        self::assertNotEmpty($response['createGuestCart']['cart']['id']);

        $guestCart = $this->guestCartRepository->get($response['createGuestCart']['cart']['id']);
        self::assertNotNull($guestCart->getId());
        self::assertNull($guestCart->getCustomer()->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testFailIfPredefinedCartIdAlreadyExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cart with ID \"572cda51902b5b517c0e1a2b2fd004b4\" already exists.");

        $predefinedCartId = '572cda51902b5b517c0e1a2b2fd004b4';

        $query = $this->getQueryWithCartId($predefinedCartId);
        $this->graphQlMutation($query);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testFailWithWrongPredefinedCartId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cart ID length should be 32 characters.");

        $predefinedCartId = '1234567890';

        $query = $this->getQueryWithCartId($predefinedCartId);
        $this->graphQlMutation($query);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
mutation {
  createGuestCart {
    cart {
      id
    }
  }
}
QUERY;
    }

    /**
     * @return string
     */
    private function getQueryWithCartId($predefinedCartId): string
    {
        return <<<QUERY
mutation {
  createGuestCart (input: {cart_uid: "{$predefinedCartId}"}) {
    cart {
      id
    }
  }
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
