<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Exception;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for create customer cart
 */
class CreateCustomerCart extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private CustomerTokenServiceInterface $customerTokenService;

    /**
     * Setup for create customer cart
     *
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        /** @var Quote $quote */
        $quoteCollection = $this->objectManager->create(Collection::class);
        foreach ($quoteCollection as $quote) {
            $quote->delete();
        }
        parent::tearDown();
    }

    /**
     * Test to create empty cart for customer with billing and shipping address
     *
     * @return void
     * @throws AuthenticationException
     * @throws Exception
     */
    #[
        ConfigFixture('customer/account_share/scope', 0),
        DataFixture(WebsiteFixture::class, ['code' => 'website2'], as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['website_id' => '$website2.id$'], 'store2'),
        ConfigFixture('general/country/allow', 'US', ScopeInterface::SCOPE_WEBSITE, 'base'),
        ConfigFixture('general/country/allow', 'NL', ScopeInterface::SCOPE_WEBSITE, 'website2'),
        DataFixture(
            Customer::class,
            [
                'email' => 'john@doe.com',
                'password' => 'test@123',
                'addresses' => [
                    [
                        'country_id' => 'US',
                        'region_id' => 32,
                        'city' => 'Boston',
                        'street' => ['10 Milk Street'],
                        'postcode' => '02108',
                        'telephone' => '1234567890',
                        'default_billing' => true,
                        'default_shipping' => true,
                    ],
                ],
            ],
            'customer'
        ),
    ]
    public function testToCreateEmptyCartForCustomerWithDefaultAddress()
    {
        $store = $this->fixtures->get('store2');
        $customerCartQuery = $this->getCustomerCartQuery();
        $headerMap = $this->getHeaderMap();
        $headerMap['Store'] = $store->getCode();
        $response = $this->graphQlMutation($customerCartQuery, [], '', $headerMap);
        self::assertArrayHasKey('customerCart', $response);
        self::assertArrayHasKey('id', $response['customerCart']);
        self::assertNotEmpty($response['customerCart']['id']);
    }

    /**
     * Query customer cart
     *
     * @return string
     */
    private function getCustomerCartQuery(): string
    {
        return <<<QUERY
{
  customerCart {
    id
  }
}
QUERY;
    }

    /**
     * Get Authentication header
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getHeaderMap(): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken('john@doe.com', 'test@123');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
