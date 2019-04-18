<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Api\GuestCartRepositoryInterface;

/**
 * Test for empty cart creation mutation for customer
 */
class CreateEmptyCartTest extends GraphQlAbstract
{
    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var string
     */
    private $maskedQuoteId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->maskedQuoteIdToQuoteId = $objectManager->get(MaskedQuoteIdToQuoteIdInterface::class);
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

        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertEquals('default', $guestCart->getStore()->getCode());
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

        /* guestCartRepository is used for registered customer to get the cart hash */
        $guestCart = $this->guestCartRepository->get($response['createEmptyCart']);
        $this->maskedQuoteId = $response['createEmptyCart'];

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertEquals('fixture_second_store', $guestCart->getStore()->getCode());
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

    public function tearDown()
    {
        if (null !== $this->maskedQuoteId) {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($this->maskedQuoteId);

            $quote = $this->quoteFactory->create();
            $this->quoteResource->load($quote, $quoteId);
            $this->quoteResource->delete($quote);

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quoteId)
                ->delete();
        }
        parent::tearDown();
    }
}
