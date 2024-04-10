<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\CustomerGraphQl\Model\Resolver\Customer as CustomerResolver;
use Magento\NewsletterGraphQl\Model\Resolver\IsSubscribed;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResolverCacheAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

/**
 * Test for customer resolver cache
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerTest extends ResolverCacheAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->graphQlResolverCache = $this->objectManager->get(
            GraphQlResolverCache::class
        );

        $this->customerRepository = $this->objectManager->get(
            CustomerRepositoryInterface::class
        );

        $this->websiteRepository = $this->objectManager->get(
            WebsiteRepositoryInterface::class
        );

        // first register secure area so we have permission to delete customer in tests
        $this->registry = $this->objectManager->get(Registry::class);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        // reset secure area to false (was set to true in setUp so we could delete customer in tests)
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * @param callable $invalidationMechanismCallable
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @dataProvider invalidationMechanismProvider
     */
    public function testCustomerResolverCacheAndInvalidation(callable $invalidationMechanismCallable)
    {
        $customer = $this->customerRepository->get('customer@example.com');

        $query = $this->getCustomerQuery();

        $token = $this->generateCustomerToken($customer->getEmail(), 'password');

        $this->mockCustomerUserInfoContext($customer);
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertCurrentCustomerCacheRecordExists($customer);

        // call query again to ensure no errors are thrown
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        // change customer data
        $invalidationMechanismCallable($customer, $token);
        // assert that cache entry is invalidated
        $this->assertCurrentCustomerCacheRecordDoesNotExist();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     */
    public function testCustomerIsSubscribedResolverCacheAndInvalidation()
    {
        /** @var SubscriptionManagerInterface $subscriptionManager */
        $subscriptionManager = $this->objectManager->get(SubscriptionManagerInterface::class);
        $customer = $this->customerRepository->get('customer@example.com');
        // unsubscribe customer to initialize state
        $subscriptionManager->unsubscribeCustomer((int)$customer->getId(), (int)$customer->getStoreId());

        $query = $this->getCustomerQuery();

        $token = $this->generateCustomerToken($customer->getEmail(), 'password');

        $this->mockCustomerUserInfoContext($customer);
        $response = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );
        $this->assertFalse($response['body']['customer']['is_subscribed']);
        $this->assertCurrentCustomerCacheRecordExists($customer);
        $this->assertIsSubscribedRecordExists($customer, false);

        // call query again to ensure no errors are thrown
        $response = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertFalse($response['body']['customer']['is_subscribed']);

        // change customer subscription
        $subscriptionManager->subscribeCustomer((int)$customer->getId(), (int)$customer->getStoreId());
        $this->assertIsSubscribedRecordNotExists($customer);
        $this->assertCurrentCustomerCacheRecordExists($customer);

        // query customer again so that subscription cache record is created
        $response = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );
        $this->assertTrue($response['body']['customer']['is_subscribed']);

        $this->assertIsSubscribedRecordExists($customer, true);
        // unsubscribe customer to restore original state
        $subscriptionManager->unsubscribeCustomer((int)$customer->getId(), (int)$customer->getStoreId());
        $this->assertIsSubscribedRecordNotExists($customer);
    }

    /**
     * Prepare cache key for subscription flag cache record.
     *
     * @param CustomerInterface $customer
     * @return string
     */
    private function getCacheKeyForIsSubscribedResolver(CustomerInterface $customer): string
    {
        $resolverMock = $this->getMockBuilder(IsSubscribed::class)->disableOriginalConstructor()->getMock();
        /** @var ProviderInterface $cacheKeyCalculatorProvider */
        $cacheKeyCalculatorProvider = Bootstrap::getObjectManager()->get(ProviderInterface::class);
        $cacheKeyFactor = $cacheKeyCalculatorProvider
            ->getKeyCalculatorForResolver($resolverMock)
            ->calculateCacheKey(
                ['model' => $customer]
            );
        $cacheKeyQueryPayloadMetadata = IsSubscribed::class . '\Interceptor[]';
        $cacheKeyParts = [
            GraphQlResolverCache::CACHE_TAG,
            $cacheKeyFactor,
            sha1($cacheKeyQueryPayloadMetadata)
        ];
        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheKeyParts));
    }

    /**
     * Assert subscription cache record exists for the given customer.
     *
     * @param CustomerInterface $customer
     * @param bool $expectedValue
     * @return void
     */
    private function assertIsSubscribedRecordExists(CustomerInterface $customer, bool $expectedValue)
    {
        $cacheKey = $this->getCacheKeyForIsSubscribedResolver($customer);
        $cacheEntry = Bootstrap::getObjectManager()->get(GraphQlResolverCache::class)->load($cacheKey);
        $this->assertIsString($cacheEntry);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEquals(
            $expectedValue,
            $cacheEntryDecoded
        );
    }

    /**
     * Assert subscription cache record does not exist for the given customer.
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function assertIsSubscribedRecordNotExists(CustomerInterface $customer)
    {
        $cacheKey = $this->getCacheKeyForIsSubscribedResolver($customer);
        $cacheEntry = Bootstrap::getObjectManager()->get(GraphQlResolverCache::class)->load($cacheKey);
        $this->assertFalse($cacheEntry);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     */
    public function testCustomerResolverCacheInvalidationOnStoreChange()
    {
        $customer = $this->customerRepository->get('customer@example.com');

        $query = $this->getCustomerQuery();

        $token = $this->generateCustomerToken($customer->getEmail(), 'password');

        $this->mockCustomerUserInfoContext($customer);
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        $this->assertCurrentCustomerCacheRecordExists($customer);

        // call query again to ensure no errors are thrown
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        // change customer data
        $storeManager = Bootstrap::getObjectManager()->get(
            StoreManagerInterface::class
        );
        $secondStore = $storeManager->getStore('fixture_second_store');
        $customer->setStoreId($secondStore->getId());
        $this->customerRepository->save($customer);
        // assert that cache entry is invalidated
        $this->assertCurrentCustomerCacheRecordDoesNotExist();
    }

    /**
     * Assert that cache record exists for the given customer.
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function assertCurrentCustomerCacheRecordExists(CustomerInterface $customer)
    {
        $cacheKey = $this->getCacheKeyForCustomerResolver();
        $cacheEntry = Bootstrap::getObjectManager()->get(GraphQlResolverCache::class)->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEquals(
            $customer->getEmail(),
            $cacheEntryDecoded['email']
        );
    }

    /**
     * Assert that cache record does not exist for the given customer.
     *
     * @return void
     */
    private function assertCurrentCustomerCacheRecordDoesNotExist()
    {
        $cacheKey = $this->getCacheKeyForCustomerResolver();
        $this->assertFalse(
            Bootstrap::getObjectManager()->get(GraphQlResolverCache::class)->test($cacheKey)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @return void
     */
    public function testCustomerResolverCacheGeneratesSeparateEntriesForEachCustomer()
    {
        $customer1 = $this->customerRepository->get('customer@example.com');
        $customer2 = $this->customerRepository->get('customer_two@example.com');

        $query = $this->getCustomerQuery();

        // query customer1
        $customer1Token = $this->generateCustomerToken(
            $customer1->getEmail(),
            'password'
        );

        $this->mockCustomerUserInfoContext($customer1);
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customer1Token]
        );

        $customer1CacheKey = $this->getCacheKeyForCustomerResolver();

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($customer1CacheKey)
        );

        // query customer2
        $this->mockCustomerUserInfoContext($customer2);
        $customer2Token = $this->generateCustomerToken(
            $customer2->getEmail(),
            'password'
        );

        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customer2Token]
        );

        $customer2CacheKey = $this->getCacheKeyForCustomerResolver();

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($customer2CacheKey)
        );

        $this->assertNotEquals(
            $customer1CacheKey,
            $customer2CacheKey
        );

        // change customer 1 and assert customer 2 cache entry is not invalidated
        $customer1->setFirstname('NewFirstName');
        $this->customerRepository->save($customer1);

        $this->assertFalse(
            $this->graphQlResolverCache->test($customer1CacheKey)
        );

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($customer2CacheKey)
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @return void
     */
    public function testCustomerResolverCacheInvalidatesWhenCustomerGetsDeleted()
    {
        $customer = $this->customerRepository->get('customer@example.com');

        $query = $this->getCustomerQuery();
        $token = $this->generateCustomerToken(
            $customer->getEmail(),
            'password'
        );

        $this->mockCustomerUserInfoContext($customer);
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        $cacheKey = $this->getCacheKeyForCustomerResolver();

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheKey)
        );

        $this->assertTagsByCacheKeyAndCustomer($cacheKey, $customer);

        // delete customer and assert that cache entry is invalidated
        $this->customerRepository->delete($customer);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey)
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @return void
     */
    #[
        DataFixture(WebsiteFixture::class, ['code' => 'website2'], 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$', 'code' => 'store2'], 'store2'),
        DataFixture(
            CustomerFixture::class,
            [
                'firstname' => 'Customer1',
                'email' => 'same_email@example.com',
                'store_id' => '1' // default store
            ]
        ),
        DataFixture(
            CustomerFixture::class,
            [
                'firstname' => 'Customer2',
                'email' => 'same_email@example.com',
                'website_id' => '$website2.id$',
            ]
        )
    ]
    public function testCustomerWithSameEmailInTwoSeparateWebsitesKeepsSeparateCacheEntries()
    {
        $website2 = $this->websiteRepository->get('website2');

        $customer1 = $this->customerRepository->get('same_email@example.com');
        $customer2 = $this->customerRepository->get('same_email@example.com', $website2->getId());

        $query = $this->getCustomerQuery();

        // query customer1
        $customer1Token = $this->generateCustomerToken(
            $customer1->getEmail(),
            'password'
        );

        $this->mockCustomerUserInfoContext($customer1);
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $customer1Token]
        );

        $customer1CacheKey = $this->getCacheKeyForCustomerResolver();
        $customer1CacheEntry = $this->graphQlResolverCache->load($customer1CacheKey);
        $customer1CacheEntryDecoded = json_decode($customer1CacheEntry, true);
        $this->assertEquals(
            $customer1->getFirstname(),
            $customer1CacheEntryDecoded['firstname']
        );

        // query customer2
        $this->mockCustomerUserInfoContext($customer2);
        $customer2Token = $this->generateCustomerToken(
            $customer2->getEmail(),
            'password',
            'store2'
        );

        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $customer2Token,
                'Store' => 'store2',
            ]
        );

        $customer2CacheKey = $this->getCacheKeyForCustomerResolver();

        $customer2CacheEntry = $this->graphQlResolverCache->load($customer2CacheKey);
        $customer2CacheEntryDecoded = json_decode($customer2CacheEntry, true);
        $this->assertEquals(
            $customer2->getFirstname(),
            $customer2CacheEntryDecoded['firstname']
        );

        // change customer 1 and assert customer 2 cache entry is not invalidated
        $customer1->setFirstname('NewFirstName');
        $this->customerRepository->save($customer1);

        $this->assertFalse(
            $this->graphQlResolverCache->test($customer1CacheKey)
        );

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($customer2CacheKey)
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @return void
     */
    public function testGuestQueryingCustomerDoesNotGenerateResolverCacheEntry()
    {
        $query = $this->getCustomerQuery();

        try {
            $this->graphQlQueryWithResponseHeaders(
                $query
            );
            $this->fail('Expected exception not thrown');
        } catch (ResponseContainsErrorsException $e) {
            // expected exception
        }

        $cacheKey = $this->getCacheKeyForCustomerResolver();

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey)
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testCustomerQueryingCustomerWithDifferentStoreHeaderDoesNotGenerateResolverCacheEntry()
    {
        $customer = $this->customerRepository->get('customer@example.com');

        $query = $this->getCustomerQuery();
        $token = $this->generateCustomerToken(
            $customer->getEmail(),
            'password'
        );

        $lowLevelFrontendCache = $this->graphQlResolverCache->getLowLevelFrontend();

        $originalTagCount = count(
            $lowLevelFrontendCache->getIdsMatchingTags([$this->graphQlResolverCache::CACHE_TAG])
        );

        $this->mockCustomerUserInfoContext($customer);

        // query customer with default store header
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Authorization' => 'Bearer ' . $token]
        );

        $tagCountAfterQueryingInDefaultStore = count(
            $lowLevelFrontendCache->getIdsMatchingTags([$this->graphQlResolverCache::CACHE_TAG])
        );

        $this->assertGreaterThan(
            $originalTagCount,
            $tagCountAfterQueryingInDefaultStore
        );

        // query customer with second store header
        $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $token,
                'Store' => 'fixture_second_store',
            ]
        );

        $tagCountAfterQueryingInSecondStore = count(
            $lowLevelFrontendCache->getIdsMatchingTags([$this->graphQlResolverCache::CACHE_TAG])
        );

        // if tag count after second store query is same as after default store query, no new tags have been created
        // and we can assume no separate cache entry has been generated
        $this->assertEquals(
            $tagCountAfterQueryingInDefaultStore,
            $tagCountAfterQueryingInSecondStore
        );
    }

    public function invalidationMechanismProvider(): array
    {
        // provider is invoked before setUp() is called so need to init here
        $repo = Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class
        );
        return [
            'change firstname' => [
                function (CustomerInterface $customer) use ($repo) {
                    $customer->setFirstname('SomeNewFirstName');
                    $repo->save($customer);
                },
            ],
            'change is_subscribed' => [
                function (CustomerInterface $customer) use ($repo) {
                    $isCustomerSubscribed = $customer->getExtensionAttributes()->getIsSubscribed();
                    $customer->getExtensionAttributes()->setIsSubscribed(!$isCustomerSubscribed);
                    $repo->save($customer);
                },
            ],
            'add and delete address' => [
                function (CustomerInterface $customer, $tokenString) {
                    // create new address because default billing address cannot be deleted
                    $this->graphQlMutation(
                        $this->getCreateAddressMutation("4000 Polk St"),
                        [],
                        '',
                        ['Authorization' => 'Bearer ' . $tokenString]
                    );
                    // query for customer to cache data after address creation
                    $result = $this->graphQlQuery(
                        $this->getCustomerQuery(),
                        [],
                        '',
                        ['Authorization' => 'Bearer ' . $tokenString]
                    );
                    // assert that cache record exists for given customer
                    $this->assertCurrentCustomerCacheRecordExists($customer);

                    $addressId = $result['customer']['addresses'][1]['id'];
                    $result = $this->graphQlMutation(
                        $this->getDeleteAddressMutation($addressId),
                        [],
                        '',
                        ['Authorization' => 'Bearer ' . $tokenString]
                    );
                    $this->assertTrue($result['deleteCustomerAddress']);
                },
            ],
            'update address' => [
                function (CustomerInterface $customer, $tokenString) {
                    // query for customer to cache data after address creation
                    $result = $this->graphQlQuery(
                        $this->getCustomerQuery(),
                        [],
                        '',
                        ['Authorization' => 'Bearer ' . $tokenString]
                    );

                    $addressId = $result['customer']['addresses'][0]['id'];
                    $result = $this->graphQlMutation(
                        $this->getUpdateAddressStreetMutation($addressId, "8000 New St"),
                        [],
                        '',
                        ['Authorization' => 'Bearer ' . $tokenString]
                    );
                    $this->assertEquals($addressId, $result['updateCustomerAddress']['id']);
                    $this->assertEquals("8000 New St", $result['updateCustomerAddress']['street'][0]);
                },
            ],
        ];
    }

    /**
     * @param string $streetAddress
     * @return string
     */
    private function getCreateAddressMutation($streetAddress)
    {
        return <<<MUTATIONCREATE
mutation{
    createCustomerAddress(input: {
        city: "Houston",
        company: "Customer Company",
        country_code: US,
        fax: "12341234567",
        firstname: "User",
        lastname: "Lastname",
        postcode: "77023",
        region: {
            region_code: "TX",
            region_id: 57
        },
        street: ["{$streetAddress}"],
        telephone: "12340987654"
    }) {
        city
        country_code
        firstname
        id
        lastname
        postcode
        region_id
        street
        telephone
    }
}
MUTATIONCREATE;
    }

    /**
     * @param int $addressId
     * @param string $streetAddress
     * @return string
     */
    private function getUpdateAddressStreetMutation($addressId, $streetAddress)
    {
        return <<<MUTATIONUPDATE
mutation{
  updateCustomerAddress(
    id: {$addressId}
    input: {
      street: ["{$streetAddress}"]
    }
  ) {
    id
    street
  }
}
MUTATIONUPDATE;
    }

    /**
     * @param int $addressId
     * @return string
     */
    private function getDeleteAddressMutation($addressId)
    {
        return <<<MUTATIONDELETE
mutation{
    deleteCustomerAddress(id: {$addressId})
}
MUTATIONDELETE;
    }

    private function assertTagsByCacheKeyAndCustomer(string $cacheKey, CustomerInterface $customer): void
    {
        $lowLevelFrontendCache = $this->graphQlResolverCache->getLowLevelFrontend();
        $cacheIdPrefix = $lowLevelFrontendCache->getOption('cache_id_prefix');
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheKey);
        $tags = $metadatas['tags'];

        $this->assertEqualsCanonicalizing(
            [
                $cacheIdPrefix . strtoupper(Customer::ENTITY) . '_' . $customer->getId(),
                $cacheIdPrefix . strtoupper(GraphQlResolverCache::CACHE_TAG),
                $cacheIdPrefix . 'MAGE',
            ],
            $tags
        );
    }

    private function getCacheKeyForCustomerResolver(): string
    {
        $resolverMock = $this->getMockBuilder(CustomerResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ProviderInterface $cacheKeyCalculatorProvider */
        $cacheKeyCalculatorProvider = Bootstrap::getObjectManager()->get(ProviderInterface::class);

        $cacheKeyFactor = $cacheKeyCalculatorProvider
            ->getKeyCalculatorForResolver($resolverMock)
            ->calculateCacheKey();

        $cacheKeyQueryPayloadMetadata = CustomerResolver::class . '\Interceptor[]';

        $cacheKeyParts = [
            GraphQlResolverCache::CACHE_TAG,
            $cacheKeyFactor,
            sha1($cacheKeyQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheKeyParts));
    }

    private function getCustomerQuery(): string
    {
        return <<<QUERY
        {
          customer {
            id
            firstname
            lastname
            email
            is_subscribed
            addresses {
              id
              street
              city
              region {
                region
              }
              postcode
            }
          }
        }
        QUERY;
    }

    /**
     * Generate customer token
     *
     * @param string $email
     * @param string $password
     * @param string $storeCode
     * @return string
     * @throws \Exception
     */
    private function generateCustomerToken(string $email, string $password, string $storeCode = 'default'): string
    {
        $query = <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;

        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            [
                'Store' => $storeCode,
            ]
        );

        return $response['generateCustomerToken']['token'];
    }
}
