<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;

use Magento\CustomerGraphQl\Model\Resolver\Customer;
use Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator;
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
     * @return void
     */
    public function testProviderForGenericKey()
    {
        $this->provider = $this->objectManager->create(Provider::class);
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $genericCalculator = $this->objectManager->get(KeyCalculator::class);
        $calc = $this->provider->getKeyCalculatorForResolver($resolver);
        $this->assertSame($genericCalculator, $calc);
    }

    /**
     * Test that customized provider returns a key calculator that provides factors in certain order.
     *
     * @return void
     */
    public function testProviderNonGenericKey()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
                'customFactorProviders' => [
                    'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                        'store' => 'Magento\StoreGraphQl\CacheIdFactorProviders\StoreProvider',
                        'currency' => 'Magento\StoreGraphQl\CacheIdFactorProviders\CurrencyProvider'
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
     * @return void
     */
    public function testProviderSameKeyCalculatorsForDifferentResolvers()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
                'customFactorProviders' => [
                    'Magento\CustomerGraphQl\Model\Resolver\Customer' => [
                        'customer_id' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\CurrentCustomerFactorProvider',
                        'is_logged_in' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\IsLoggedInProvider'
                    ],
                    'Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses' => [
                        'customer_id' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\CurrentCustomerFactorProvider',
                        'is_logged_in' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\IsLoggedInProvider'
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
        $this->assertSame($calcCustomer, $calcAddress);
    }

    /**
     * Test that different key calculators with intersecting factors are not being reused.
     *
     * @return void
     */
    public function testProviderDifferentKeyCalculatorsForDifferentResolvers()
    {
        $this->provider = $this->objectManager->create(Provider::class, [
                'customFactorProviders' => [
                    'Magento\CustomerGraphQl\Model\Resolver\Customer' => [
                        'customer_id' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\CurrentCustomerFactorProvider',
                        'is_logged_in' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\IsLoggedInProvider'
                    ],
                    'Magento\CustomerGraphQl\Model\Resolver\CustomerAddresses' => [
                        'customer_id' => 'Magento\CustomerGraphQl\CacheIdFactorProviders\CurrentCustomerFactorProvider',
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
