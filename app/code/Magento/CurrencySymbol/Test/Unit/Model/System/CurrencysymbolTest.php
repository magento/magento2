<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Model\System;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Unserialize\SecureUnserializer;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\CurrencySymbol\Model\System\Currencysymbol
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencysymbolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemStoreMock;

    /**
     * @var \Magento\Config\Model\Config\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactoryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    protected $model;

    /**
     * @var SecureUnserializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unserializerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->localeResolverMock = $this->getMock(
            \Magento\Framework\Locale\ResolverInterface::class,
            [
                'getLocale',
                'getDefaultLocalePath',
                'setDefaultLocale',
                'getDefaultLocale',
                'setLocale',
                'emulate',
                'revert',
            ],
            [],
            '',
            false
        );
        $this->systemStoreMock = $this->getMock(
            \Magento\Store\Model\System\Store::class,
            ['getWebsiteCollection', 'getGroupCollection', 'getStoreCollection'],
            [],
            '',
            false
        );
        $this->configFactoryMock = $this->getMock(
            \Magento\Config\Model\Config\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMock(
            \Magento\Framework\Event\ManagerInterface::class,
            ['dispatch'],
            [],
            '',
            false
        );
        $this->coreConfigMock = $this->getMock(
            \Magento\Framework\App\Config\ReinitableConfigInterface::class,
            ['reinit', 'setValue', 'getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->cacheTypeListMock = $this->getMock(
            \Magento\Framework\App\Cache\TypeListInterface::class,
            [],
            [],
            '',
            false
        );
        $this->unserializerMock = $this->getMockBuilder(SecureUnserializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical'])
            ->getMockForAbstractClass();
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'localeResolver' => $this->localeResolverMock,
                'systemStore' => $this->systemStoreMock,
                'configFactory' => $this->configFactoryMock,
                'eventManager' => $this->eventManagerMock,
                'coreConfig' => $this->coreConfigMock,
                'storeManager' => $this->storeManagerMock,
                'cacheTypeList' => $this->cacheTypeListMock,
                'unserializer' => $this->unserializerMock,
                'logger' => $this->loggerMock,
            ]
        );
    }

    protected function tearDown()
    {
        unset($this->objectManagerHelper);
    }

    public function testGetCurrencySymbolData()
    {
        $expectedSymbolsData = [
            'EUR' => [
                'parentSymbol' => '€',
                'displayName' => 'Euro',
                'displaySymbol' => '€',
                'inherited' => true,
            ],
            'USD' => [
                'parentSymbol' => '$',
                'displayName' => 'US Dollar',
                'displaySymbol' => '$',
                'inherited' => true,
            ],
        ];
        $websiteId = 1;
        $groupId = 2;
        $currencies = 'USD,EUR';

        $this->prepareMocksForGetCurrencySymbolsData($websiteId, $groupId, $currencies);
        $this->assertEquals($expectedSymbolsData, $this->model->getCurrencySymbolsData());
    }

    public function testSetCurrencySymbolData()
    {
        $websiteId = 1;
        $groupId = 2;
        $currencies = 'USD,EUR';
        $symbols = [];
        $value['options']['fields']['customsymbol']['inherit'] = 1;

        $this->prepareMocksForGetCurrencySymbolsData($websiteId, $groupId, $currencies);

        /**
         * @var \Magento\Config\Model\Config|\PHPUnit_Framework_MockObject_MockObject
         */
        $configMock = $this->getMock(
            \Magento\Config\Model\Config::class,
            ['setSection', 'setWebsite', 'setStore', 'setGroups', 'save'],
            [],
            '',
            false
        );

        $this->configFactoryMock->expects($this->any())->method('create')->willReturn($configMock);
        $configMock->expects($this->any())
            ->method('setSection')
            ->with(Currencysymbol::CONFIG_SECTION)
            ->willReturnSelf();
        $configMock->expects($this->any())->method('setWebsite')->with(null)->willReturnSelf();
        $configMock->expects($this->any())->method('setStore')->with(null)->willReturnSelf();
        $configMock->expects($this->any())->method('setGroups')->with($value)->willReturnSelf();

        $this->coreConfigMock->expects($this->once())->method('reinit');
        $this->cacheTypeListMock->expects($this->atLeastOnce())->method('invalidate');

        $this->eventManagerMock->expects($this->atLeastOnce())->method('dispatch')->willReturnMap(
            [
                ['admin_system_config_changed_section_currency_before_reinit', null, null],
                ['admin_system_config_changed_section_currency', null, null]
            ]
        );

        $this->assertInstanceOf(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class,
            $this->model->setCurrencySymbolsData($symbols)
        );
    }

    /**
     * @dataProvider getCurrencySymbolDataProvider
     * @param string $code
     * @param string $expectedSymbol
     * @param string $serializedCustomSymbols
     *
     * @return void
     */
    public function testGetCurrencySymbol($code, $expectedSymbol, $serializedCustomSymbols)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $serializedCustomSymbols,
                    ],
                ]
            );
        $this->unserializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturn([$code => $expectedSymbol]);
        
        $currencySymbol = $this->model->getCurrencySymbol($code);
        $this->assertEquals($expectedSymbol, $currencySymbol);
    }

    /**
     * @return array
     */
    public function getCurrencySymbolDataProvider()
    {
        return [
            'existentCustomSymbol' => [
                'code' => 'USD',
                'expectedSymbol' => '$',
                'serializedCustomSymbols' => 'a:1:{s:3:"USD";s:1:"$";}',
            ],
            'nonExistentCustomSymbol' => [
                'code' => 'UAH',
                'expectedSymbol' => false,
                'serializedCustomSymbols' => 'a:1:{s:3:"USD";s:1:"$";}',
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetBadCurrencySymbol()
    {
        $serializedBadCustomSymbols = 'a:1:{i:0;O:8:"stdClass":0:{}}';
        $exceptionMessage = 'Data contains serialized object and cannot be unserialized';
        $exception = new \InvalidArgumentException($exceptionMessage);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $serializedBadCustomSymbols,
                    ],
                ]
            );
        $this->unserializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedBadCustomSymbols)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);

        $this->assertEquals(false, $this->model->getCurrencySymbol(''));
    }

    /**
     * Prepare mocks for getCurrencySymbolsData
     *
     * @param int $websiteId
     * @param int $groupId
     * @param string $currencies
     */
    protected function prepareMocksForGetCurrencySymbolsData($websiteId, $groupId, $currencies)
    {
        /**
         * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
         */
        $websiteMock = $this->getMock(\Magento\Store\Model\Website::class, ['getId', 'getConfig'], [], '', false);

        /**
         * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
         */
        $groupMock = $this->getMock(\Magento\Store\Model\Group::class, ['getId', 'getWebsiteId'], [], '', false);

        /**
         * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
         */
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getGroupId'], [], '', false);

        $this->systemStoreMock->expects($this->once())
            ->method('getWebsiteCollection')
            ->willReturn([$websiteMock]);
        $this->systemStoreMock->expects($this->once())->method('getGroupCollection')->willReturn([$groupMock]);
        $this->systemStoreMock->expects($this->once())->method('getStoreCollection')->willReturn([$storeMock]);
        $websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $groupMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $groupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $storeMock->expects($this->any())->method('getGroupId')->willReturn($groupId);

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap(
                [
                    [Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL, ScopeInterface::SCOPE_STORE, null, ''],
                    [
                        Currencysymbol::XML_PATH_ALLOWED_CURRENCIES,
                        ScopeInterface::SCOPE_STORE,
                        $storeMock,
                        $currencies,
                    ],
                    [Currencysymbol::XML_PATH_ALLOWED_CURRENCIES, ScopeInterface::SCOPE_STORE, null, $currencies],
                    [
                        Currencysymbol::XML_PATH_ALLOWED_CURRENCIES,
                        ScopeInterface::SCOPE_STORE,
                        $storeMock,
                        $currencies,
                    ],
                ]
            );

        $websiteMock->expects($this->any())
            ->method('getConfig')
            ->with(Currencysymbol::XML_PATH_ALLOWED_CURRENCIES)
            ->willReturn($currencies);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en');
    }
}
