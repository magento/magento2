<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Framework\App\ResourceConnection;
use Magento\Integration\Api\CustomerTokenServiceInterface;
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->guestCartRepository = $objectManager->get(GuestCartRepositoryInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCart()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertSame('default', $guestCart->getStore()->getCode());

        $this->deleteCreatedQuote($guestCart->getId());
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateEmptyCartWithNotDefaultStore()
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;

        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken, 'Store' => 'fixture_second_store'];

        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('createEmptyCart', $response);

        $maskedCartId = $response['createEmptyCart'];
        /* guestCartRepository is used for registered customer to get the cart hash */
        $guestCart = $this->guestCartRepository->get($maskedCartId);

        self::assertNotNull($guestCart->getId());
        self::assertEquals(1, $guestCart->getCustomer()->getId());
        self::assertSame('fixture_second_store', $guestCart->getStore()->getCode());

        $this->deleteCreatedQuote($guestCart->getId());
    }

    /**
     * Delete active quote for customer by customer id.
     * This is needed to have ability to create new quote for another store and not return the active one.
     * @see QuoteManagement::createCustomerCart
     *
     * @param $quoteId
     */
    private function deleteCreatedQuote($quoteId)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->query('DELETE FROM quote WHERE entity_id = ' . $quoteId);
    }
}
