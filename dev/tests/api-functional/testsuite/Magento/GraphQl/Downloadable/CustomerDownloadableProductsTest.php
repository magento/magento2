<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Downloadable;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test retrieving of customer downloadable products.
 */
class CustomerDownloadableProductsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoApiDataFixture Magento/Downloadable/_files/customer_order_with_downloadable_product.php
     */
    public function testCustomerDownloadableProducts()
    {
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
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoApiDataFixture Magento/Downloadable/_files/customer_order_with_downloadable_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testGuestCannotAccessDownloadableProducts()
    {
        $this->graphQlQuery($this->getQuery());
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
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
