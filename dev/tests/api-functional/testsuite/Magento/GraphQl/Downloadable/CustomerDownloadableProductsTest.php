<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Downloadable;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Quote\Model\QuoteFactory;

/**
 * Test retrieving of customer download products
 */
class CustomerDownloadableProductsTest extends GraphQlAbstract
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteResource
     */
    protected $quoteResource;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/downloadable_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_downloadable_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     */
    public function testCustomerDownloadableProducts()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_quote', 'reserved_order_id');
        $this->cartManagement->placeOrder($quote->getId());

        $query = $this->getQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('items', $response['customerDownloadableProducts']);
        self::assertCount(1, $response['customerDownloadableProducts']['items']);

        self::assertArrayHasKey('date', $response['customerDownloadableProducts']['items'][0]);
        self::assertNotEmpty($response['customerDownloadableProducts']['items'][0]['date']);

        self::assertArrayHasKey('download_url', $response['customerDownloadableProducts']['items'][0]);
        self::assertNotEmpty($response['customerDownloadableProducts']['items'][0]['download_url']);

        self::assertArrayHasKey('order_increment_id', $response['customerDownloadableProducts']['items'][0]);
        self::assertNotEmpty($response['customerDownloadableProducts']['items'][0]['order_increment_id']);

        self::assertArrayHasKey('remaining_downloads', $response['customerDownloadableProducts']['items'][0]);
        self::assertNotEmpty($response['customerDownloadableProducts']['items'][0]['remaining_downloads']);

        self::assertArrayHasKey('status', $response['customerDownloadableProducts']['items'][0]);
        self::assertNotEmpty($response['customerDownloadableProducts']['items'][0]['status']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerHasNoOrders()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('items', $response['customerDownloadableProducts']);
        self::assertCount(0, $response['customerDownloadableProducts']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/enable_offline_payment_methods.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_checkmo_payment_method.php
     */
    public function testCustomerHasNoDownloadableProducts()
    {
        $query = $this->getQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('items', $response['customerDownloadableProducts']);
        self::assertCount(0, $response['customerDownloadableProducts']['items']);
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->cartManagement = $objectManager->get(CartManagementInterface::class);
    }

    /**
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
{
  customerDownloadableProducts {
    items {
      date
      download_url
      order_increment_id
      remaining_downloads
      status
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
