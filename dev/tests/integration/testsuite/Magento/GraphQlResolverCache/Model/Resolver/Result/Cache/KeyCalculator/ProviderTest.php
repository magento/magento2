<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\Cache\KeyCalculator;

use Magento\CustomerGraphQl\Model\Resolver\Customer;
use Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\Provider;
use Magento\StoreGraphQl\CacheIdFactorProviders\CurrencyProvider;
use Magento\StoreGraphQl\CacheIdFactorProviders\StoreProvider;
use Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Graphql Resolver-level cache key provider.
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
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        parent::setUp();
    }

    /**
     * Test that generic key provided for non-customized resolver is a generic key provider with default config.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderForGenericKey()
    {
        $this->provider = $this->objectManager->create(Provider::class);
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $genericCalculator = $this->objectManager->get(Calculator::class);
        $calc = $this->provider->getKeyCalculatorForResolver($resolver);
        $this->assertSame($genericCalculator, $calc);
    }

    /**
     * Test that customized provider returns a key calculator that provides factors in certain order.
     *
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testProviderNonGenericKey()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
                'customFactorProviders' => [
                    'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                        'store' => 'Magento\StoreGraphQl\Model\Resolver\CacheKey\FactorProvider\Store',
                        'currency' => 'Magento\StoreGraphQl\Model\Resolver\CacheKey\FactorProvider\Currency'
                    ],
                ]
            ]);
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeFactorMock = $this->getMockBuilder(StoreProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactorName', 'getFactorValue'])
            ->getMock();
        $currencyFactorMock = $this->getMockBuilder(CurrencyProvider::class)
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

        $this->objectManager->addSharedInstance($storeFactorMock, StoreProvider::class);
        $this->objectManager->addSharedInstance($currencyFactorMock, CurrencyProvider::class);
        $expectedKey = hash('sha256', strtoupper(implode('|', ['currency' => 'USD', 'store' => 'default'])));
        $calc = $this->provider->getKeyCalculatorForResolver($resolver);
        $key = $calc->calculateCacheKey();
        $this->assertNotEmpty($key);
        $this->assertEquals($expectedKey, $key);
        $this->objectManager->removeSharedInstance(StoreProvider::class);
        $this->objectManager->removeSharedInstance(CurrencyProvider::class);
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
                'customFactorProviders' => [
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
                'customFactorProviders' => [
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
