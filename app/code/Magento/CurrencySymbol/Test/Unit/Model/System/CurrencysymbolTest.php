<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CurrencySymbol\Test\Unit\Model\System;

use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CurrencysymbolTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencysymbolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object manager helper
     *
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolverMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\System\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemStoreMock;

    /**
     * @var \Magento\Config\Model\Config\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configFactoryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\CurrencySymbol\Model\System\Currencysymbol
     */
    private $model;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $this->localeResolverMock = $this->createPartialMock(
            \Magento\Framework\Locale\ResolverInterface::class,
            [
                'getLocale',
                'getDefaultLocalePath',
                'setDefaultLocale',
                'getDefaultLocale',
                'setLocale',
                'emulate',
                'revert'
            ]
        );
        $this->systemStoreMock = $this->createPartialMock(
            \Magento\Store\Model\System\Store::class,
            ['getWebsiteCollection', 'getGroupCollection', 'getStoreCollection']
        );
        $this->configFactoryMock = $this->createPartialMock(\Magento\Config\Model\Config\Factory::class, ['create']);
        $this->eventManagerMock = $this->createPartialMock(
            \Magento\Framework\Event\ManagerInterface::class,
            ['dispatch']
        );
        $this->coreConfigMock = $this->createPartialMock(
            \Magento\Framework\App\Config\ReinitableConfigInterface::class,
            ['reinit', 'setValue', 'getValue', 'isSetFlag']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cacheTypeListMock = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

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
                'serializer' => $this->serializerMock,
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
                'inherited' => true
            ],
            'USD' => [
                'parentSymbol' => '$',
                'displayName' => 'US Dollar',
                'displaySymbol' => 'custom $',
                'inherited' => false
            ]
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
        $configValue['options']['fields']['customsymbol']['inherit'] = 1;

        $this->prepareMocksForGetCurrencySymbolsData($websiteId, $groupId, $currencies);

        $this->expectSaveOfCustomSymbols($configValue);
        $this->expectApplicationServiceMethodsCalls();
        $this->assertInstanceOf(
            \Magento\CurrencySymbol\Model\System\Currencysymbol::class,
            $this->model->setCurrencySymbolsData($symbols)
        );
    }

    /**
     * Assert that config with custom currency symbols happens with expected values
     *
     * @param array $configValue
     */
    private function expectSaveOfCustomSymbols(array $configValue)
    {
        /**
         * @var \Magento\Config\Model\Config|\PHPUnit_Framework_MockObject_MockObject
         */
        $configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSection', 'setWebsite', 'setStore', 'setGroups', 'save'])
            ->getMock();

        $this->configFactoryMock->expects($this->once())->method('create')->willReturn($configMock);
        $configMock->expects($this->once())
            ->method('setSection')
            ->with(Currencysymbol::CONFIG_SECTION)
            ->willReturnSelf();
        $configMock->expects($this->once())->method('setWebsite')->with(null)->willReturnSelf();
        $configMock->expects($this->once())->method('setStore')->with(null)->willReturnSelf();
        $configMock->expects($this->once())->method('setGroups')->with($configValue)->willReturnSelf();
        $configMock->expects($this->once())->method('save');
    }

    /**
     * Assert that application service methods, such as cache cleanup and events dispatching, are called
     */
    private function expectApplicationServiceMethodsCalls()
    {
        $this->coreConfigMock->expects($this->once())->method('reinit');
        $this->cacheTypeListMock->expects($this->atLeastOnce())->method('invalidate');
        $this->eventManagerMock->expects($this->exactly(2))->method('dispatch');
    }

    /**
     * @dataProvider getCurrencySymbolDataProvider
     */
    public function testGetCurrencySymbol(
        $code,
        $expectedSymbol,
        $serializedCustomSymbols,
        $unserializedCustomSymbols
    ) {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn($serializedCustomSymbols);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedCustomSymbols)
            ->willReturn($unserializedCustomSymbols);
        $currencySymbol = $this->model->getCurrencySymbol($code);
        $this->assertEquals($expectedSymbol, $currencySymbol);
    }

    /**
     * @return array
     */
    public function getCurrencySymbolDataProvider()
    {
        return [
            'existent custom symbol' => [
                'code' => 'USD',
                'expectedSymbol' => '$',
                'serializedCustomSymbols' => '{"USD":"$"}',
                'unserializedCustomSymbols' => ['USD' => '$'],
            ],
            'nonexistent custom symbol' => [
                'code' => 'UAH',
                'expectedSymbol' => false,
                'serializedCustomSymbols' => '{"USD":"$"}',
                'unserializedCustomSymbols' => ['USD' => '$'],
            ],
        ];
    }

    public function testGetCurrencySymbolWithNoSymbolsConfig()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(
                Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL,
                ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $currencySymbol = $this->model->getCurrencySymbol('USD');
        $this->assertEquals(false, $currencySymbol);
    }

    /**
     * Prepare mocks for getCurrencySymbolsData
     *
     * @param int $websiteId
     * @param int $groupId
     * @param string $currencies
     */
    protected function prepareMocksForGetCurrencySymbolsData(
        $websiteId,
        $groupId,
        $currencies
    ) {
        $customSymbolsSerialized = '{"USD":"custom $"}';
        /**
         * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
         */
        $websiteMock = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId', 'getConfig']);

        /**
         * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
         */
        $groupMock = $this->createPartialMock(\Magento\Store\Model\Group::class, ['getId', 'getWebsiteId']);

        /**
         * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
         */
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getGroupId']);

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
                    [
                        Currencysymbol::XML_PATH_CUSTOM_CURRENCY_SYMBOL,
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $customSymbolsSerialized
                    ],
                    [
                        Currencysymbol::XML_PATH_ALLOWED_CURRENCIES,
                        ScopeInterface::SCOPE_STORE,
                        $storeMock,
                        $currencies
                    ],
                    [Currencysymbol::XML_PATH_ALLOWED_CURRENCIES, ScopeInterface::SCOPE_STORE, null, $currencies],
                    [
                        Currencysymbol::XML_PATH_ALLOWED_CURRENCIES,
                        ScopeInterface::SCOPE_STORE,
                        $storeMock,
                        $currencies
                    ]
                ]
            );

        $websiteMock->expects($this->any())
            ->method('getConfig')
            ->with(Currencysymbol::XML_PATH_ALLOWED_CURRENCIES)
            ->willReturn($currencies);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($customSymbolsSerialized)
            ->willReturn(['USD' => 'custom $']);
    }
}
