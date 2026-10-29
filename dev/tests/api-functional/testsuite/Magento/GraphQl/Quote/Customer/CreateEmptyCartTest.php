<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for empty cart creation mutation for customer
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

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
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->maskedQuoteIdToQuoteId = $objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $quoteId = $this->maskedQuoteIdToQuoteId->execute($response['createEmptyCart']);
        $cart = $this->cartRepository->get($quoteId);

        self::assertNotNull($cart->getId());
        self::assertEquals(1, $cart->getCustomer()->getId());
        self::assertEquals('default', $cart->getStore()->getCode());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyMultipleRequestsCart()
    {
        $query = $this->getQuery();
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);
        $maskedCartId = $response['createEmptyCart'];

        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        self::assertEquals($maskedCartId, $response['createEmptyCart']);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = $this->getQuery();

        $headerMap = $this->getHeaderMapWithCustomerToken();
        $headerMap['Store'] = 'fixture_second_store';
        $response = $this->graphQlMutation($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        $quoteId = $this->maskedQuoteIdToQuoteId->execute($response['createEmptyCart']);
        $cart = $this->cartRepository->get($quoteId);

        self::assertNotNull($cart->getId());
        self::assertEquals(1, $cart->getCustomer()->getId());
        self::assertEquals('fixture_second_store', $cart->getStore()->getCode());
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());

        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertEquals($predefinedCartId, $response['createEmptyCart']);

        $quoteId = $this->maskedQuoteIdToQuoteId->execute($response['createEmptyCart']);
        $cart = $this->cartRepository->get($quoteId);
        self::assertNotNull($cart->getId());
        self::assertEquals(1, $cart->getCustomer()->getId());
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
        $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMapWithCustomerToken());
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

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMapWithCustomerToken(
        string $username = 'customer@example.com',
        string $password = 'password'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
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
