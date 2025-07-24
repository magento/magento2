<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CurrencySymbol\Test\Unit\Model\System;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrencysymbolTest extends TestCase
{
    /**
     * Object manager helper
     *
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Store|MockObject
     */
    private $systemStoreMock;

    /**
     * @var Factory|MockObject
     */
    private $configFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $coreConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var TypeListInterface|MockObject
     */
    private $cacheTypeListMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var Currencysymbol
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->scopeConfigMock = $this->createPartialMock(
            ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );
        $this->localeResolverMock = $this->createPartialMock(
            ResolverInterface::class,
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
            Store::class,
            ['getWebsiteCollection', 'getGroupCollection', 'getStoreCollection']
        );
        $this->configFactoryMock = $this->createPartialMock(Factory::class, ['create']);
        $this->eventManagerMock = $this->createPartialMock(
            ManagerInterface::class,
            ['dispatch']
        );
        $this->coreConfigMock = $this->createPartialMock(
            ReinitableConfigInterface::class,
            ['reinit', 'setValue', 'getValue', 'isSetFlag']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->cacheTypeListMock = $this->getMockForAbstractClass(TypeListInterface::class);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManagerHelper->getObject(
            Currencysymbol::class,
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

    protected function tearDown(): void
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
            Currencysymbol::class,
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
         * @var Config|MockObject
         */
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->addMethods(['setSection', 'setWebsite', 'setStore', 'setGroups'])
            ->onlyMethods(['save'])
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
    public static function getCurrencySymbolDataProvider()
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
        $this->assertFalse($currencySymbol);
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
         * @var Website|MockObject
         */
        $websiteMock = $this->createPartialMock(Website::class, ['getId', 'getConfig']);

        /**
         * @var Group|MockObject
         */
        $groupMock = $this->createPartialMock(Group::class, ['getId', 'getWebsiteId']);

        /**
         * @var \Magento\Store\Model\Store|MockObject
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
