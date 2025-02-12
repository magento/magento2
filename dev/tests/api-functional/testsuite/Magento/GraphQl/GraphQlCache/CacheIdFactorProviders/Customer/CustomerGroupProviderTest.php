<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for customerGroup CacheIdFactorProvider.
 */
class CustomerGroupProviderTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var Group
     */
    private $customerGroup;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerGroup = $objectManager->get(Group::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * Tests that cache id header changes based on customer group and remains consistent for same customer group
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheIdHeaderWithCustomerGroup()
    {
        $sku = 'simple_product';
        $query = <<<QUERY
 {
           products(filter: {sku: {eq: "{$sku}"}})
           {
               items {
                   id
                   name
                   sku
                   description {
                   html
                   }
               }
           }
       }
QUERY;
        $response = $this->graphQlQueryWithResponseHeaders($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheId));
        $groupCode = 'Retailer';
        $customer = $this->customerRepository->get('customer@example.com');
        $customerGroupId = $this->customerGroup->load($groupCode, 'customer_group_code')->getId();
        // change the customer group of this customer from the default group
        $customer->setGroupId($customerGroupId);
        $this->customerRepository->save($customer);
        $responseAfterCustomerGroupChange = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseAfterCustomerGroupChange['headers']);
        $cacheIdCustomerGroupChange = $responseAfterCustomerGroupChange['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify that the the cache id generated is a 64 character long
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $cacheId));
        // check that the cache ids generated before and after customer group changes are not equal
        $this->assertNotEquals($cacheId, $cacheIdCustomerGroupChange);
        //Change the customer groupId back to Default General
        $customer->setGroupId(1);
        $this->customerRepository->save($customer);
        $responseDefaultCustomerGroup = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            $this->getHeaderMap()
        );
        $cacheIdDefaultCustomerGroup = $responseDefaultCustomerGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        //Verify that the cache id is same as original $cacheId
        $this->assertEquals($cacheIdDefaultCustomerGroup, $cacheId);
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
