<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\RateRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for tax rate CacheIdFactorProvider.
 */
class CustomerTaxRateProviderTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var Rate
     */
    private $taxRate;

    /**
     * @var RateRepository
     */
    private $taxRateRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->taxRate = $objectManager->get(Rate::class);
        $this->taxRateRepository = $objectManager->get(RateRepository::class);
    }

    /**
     * Tests that cache id header changes based on the customer tax rate and remains consistent for the
     * same customer tax rate
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_shipping_address_36104.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheIdHeaderWithCustomerTaxRate()
    {
        $query = <<<QUERY
 {
  products( filter:{})
  {
    total_count
    items {
      name
      sku
      price_range {
        minimum_price {
          final_price {value }
          regular_price {value }
        }
        maximum_price{
         final_price{ value }
         regular_price{ value } }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQueryWithResponseHeaders($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheId));
        //Change tax rate to different value
        /** @var Rate $rate */
        $rate = $this->taxRate->loadByCode('US-AL-*-Rate-1');
        $rate->setRate(10);
        $this->taxRateRepository->save($rate);
        $responseAfterTaxRateChange = $this->graphQlQueryWithResponseHeaders($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseAfterTaxRateChange['headers']);
        $cacheIdTaxRateChange = $responseAfterTaxRateChange['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify that the the cache id generated is a 64 character long
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheId));
        // check that the cache ids generated before and after tax rate changes are not equal
        $this->assertNotEquals($cacheId, $cacheIdTaxRateChange);

        //Change the tax rate back to original 7.5
        /** @var Rate $rate */
        $rate = $this->taxRate->loadByCode('US-AL-*-Rate-1');
        $rate->setRate(7.5);
        $this->taxRateRepository->save($rate);
        $responseOriginalTaxRate = $this->graphQlQueryWithResponseHeaders($query, [], '', $this->getHeaderMap());
        $cacheIdOriginalTaxRate = $responseOriginalTaxRate['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        //Verify that the cache id is same as original $cacheId
        $this->assertEquals($cacheIdOriginalTaxRate, $cacheId);
    }

    /**
     * Authentication header map
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     *
     * @throws AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
