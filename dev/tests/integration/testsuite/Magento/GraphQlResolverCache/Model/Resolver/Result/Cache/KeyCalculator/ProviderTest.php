<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\KeyCalculator;

use Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\CustomerGroup;
use Magento\CustomerGraphQl\Model\Resolver\Customer;
use Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\Provider;
use Magento\StoreGraphQl\CacheIdFactorProviders\CurrencyProvider;
use Magento\StoreGraphQl\CacheIdFactorProviders\StoreProvider;
use Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Graphql Resolver-level cache key provider.
 * @magentoAppArea graphql
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * Dev docs link
     */
    private const DEV_DOCS = "https://developer.adobe.com/commerce/webapi/graphql/develop";

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    /**
     * Test that missing config triggers an exception.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderNotConfigured()
    {
        $this->provider = $this->objectManager->create(Provider::class);
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->expectException(\InvalidArgumentException::class);
        $resolverClass = get_class($resolver);
        $devDocs = self::DEV_DOCS;
        $this->expectExceptionMessage(
            "GraphQL Resolver Cache key factors are not determined for {$resolverClass} or its parents. " .
            "See {$devDocs} for information about configuring cache key factors for a resolver."
        );
        $this->provider->getKeyCalculatorForResolver($resolver);
    }

    /**
     * Test that empty provided config is handled properly.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderEmptyConfig()
    {
        $this->provider = $this->objectManager->create(
            Provider::class,
            [
                'factorProviders' => [
                    'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [],
                ]
            ]
        );
        $resolver = $this->getMockBuilder(\Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $calc = $this->provider->getKeyCalculatorForResolver($resolver);
        $this->assertNull($calc->calculateCacheKey());
    }

    /**
     * Test that customized provider returns a key calculator that provides factors in certain order.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderKeyFactorsConfigured()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
            'factorProviders' => [
                'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                    'store' => 'Magento\StoreGraphQl\Model\Resolver\CacheKey\FactorProvider\Store',
                    'currency' => 'Magento\StoreGraphQl\Model\Resolver\CacheKey\FactorProvider\Currency'
                ],
                'StoreConfigDerivedMock' => [
                    'customer_group' => 'Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\CustomerGroup'
                ]
            ]
        ]);
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->setMockClassName('StoreConfigDerivedMock')
            ->getMock();
        $storeFactorMock = $this->getMockBuilder(StoreProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMock();
        $currencyFactorMock = $this->getMockBuilder(CurrencyProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMock();
        $customerGroupFactorMock = $this->getMockBuilder(CustomerGroup::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMock();
        $storeFactorMock->expects($this->any())
            ->method('getFactorName')
            ->withAnyParameters()
            ->willReturn(StoreProvider::NAME);
        $storeFactorMock->expects($this->any())
            ->method('getFactorValue')
            ->withAnyParameters()
            ->willReturn('default');

        $currencyFactorMock->expects($this->any())
            ->method('getFactorName')
            ->withAnyParameters()
            ->willReturn(CurrencyProvider::NAME);
        $currencyFactorMock->expects($this->any())
            ->method('getFactorValue')
            ->withAnyParameters()->willReturn('USD');

        $customerGroupFactorMock->expects($this->any())
            ->method('getFactorName')
            ->withAnyParameters()
            ->willReturn('CUSTOMER_GROUP');
        $customerGroupFactorMock->expects($this->any())
            ->method('getFactorValue')
            ->withAnyParameters()
            ->willReturn('1');

        $this->objectManager->addSharedInstance($storeFactorMock, StoreProvider::class);
        $this->objectManager->addSharedInstance($currencyFactorMock, CurrencyProvider::class);
        $this->objectManager->addSharedInstance($customerGroupFactorMock, CustomerGroup::class);
        $salt = $this->objectManager->get(DeploymentConfig::class)
            ->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        $expectedKey = hash(
            'sha256',
            strtoupper(implode('|', ['CURRENCY' => 'USD', 'CUSTOMER_GROUP' => '1', 'STORE' => 'default'])) . "|$salt"
        );
        $calc = $this->provider->getKeyCalculatorForResolver($resolver);
        $key = $calc->calculateCacheKey();
        $this->assertNotEmpty($key);
        $this->assertEquals($expectedKey, $key);
        $this->objectManager->removeSharedInstance(StoreProvider::class);
        $this->objectManager->removeSharedInstance(CurrencyProvider::class);
        $this->objectManager->removeSharedInstance(CustomerGroup::class);
    }

    /**
     * Test that if different resolvers have same custom key calculator it is not instantiated again.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderSameKeyCalculatorsForDifferentResolvers()
    {
        $this->provider = $this->objectManager->create(
            Provider::class,
            [
                'factorProviders' => [
                    'Magento\CustomerGraphQl\Model\Resolver\Customer' => [
                        'customer_id' =>
                            'Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\CurrentCustomerId',
                        'is_logged_in' => 'Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\IsLoggedIn'
                    ],
                    'Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses' => [
                        'customer_id' =>
                            'Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\CurrentCustomerId',
                        'is_logged_in' => 'Magento\CustomerGraphQl\Model\Resolver\CacheKey\FactorProvider\IsLoggedIn'
                    ]
                ]
            ]
        );
        $customerResolver = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerAddressResolver = $this->getMockBuilder(CustomerAddresses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $calcCustomer = $this->provider->getKeyCalculatorForResolver($customerResolver);
        $calcAddress = $this->provider->getKeyCalculatorForResolver($customerAddressResolver);
        $this->assertSame($calcCustomer, $calcAddress);
    }

    /**
     * Test that different key calculators with intersecting factors are not being reused.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderDifferentKeyCalculatorsForDifferentResolvers()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
                'factorProviders' => [
                    'Magento\CustomerGraphQl\Model\Resolver\Customer' => [
                        'customer_id' =>
                            'Magento\CustomerGraphQl\Model\Resolver\Cache\KeyFactorProvider\CurrentCustomerId',
                        'is_logged_in' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\IsLoggedInProvider'
                    ],
                    'Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses' => [
                        'customer_id' =>
                            'Magento\CustomerGraphQl\Model\Resolver\Cache\KeyFactorProvider\CurrentCustomerId',
                    ]
                ]
            ]);
        $customerResolver = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerAddressResolver = $this->getMockBuilder(CustomerAddresses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $calcCustomer = $this->provider->getKeyCalculatorForResolver($customerResolver);
        $calcAddress = $this->provider->getKeyCalculatorForResolver($customerAddressResolver);
        $this->assertNotSame($calcCustomer, $calcAddress);
    }
}
