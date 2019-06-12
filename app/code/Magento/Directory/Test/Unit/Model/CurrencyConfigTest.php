<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Config\App\Config\Type\System;
use Magento\Directory\Model\CurrencyConfig;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CurrencyConfig model.
 */
class CurrencyConfigTest extends TestCase
{
    /**
     * @var CurrencyConfig
     */
    private $testSubject;

    /**
     * @var System|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStores', 'getWebsites'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->testSubject = $objectManager->getObject(
            CurrencyConfig::class,
            [
                'storeManager' => $this->storeManager,
                'appState' => $this->appState,
                'config' => $this->config,
            ]
        );
    }

    /**
<<<<<<< HEAD
     * Test get currency config for admin and storefront areas.
=======
     * Test get currency config for admin, crontab and storefront areas.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @dataProvider getConfigCurrenciesDataProvider
     * @return void
     */
    public function testGetConfigCurrencies(string $areCode)
    {
        $path = 'test/path';
        $expected = ['ARS', 'AUD', 'BZD'];

        $this->appState->expects(self::once())
            ->method('getAreaCode')
            ->willReturn($areCode);

        /** @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects(self::once())
            ->method('getCode')
            ->willReturn('testCode');

<<<<<<< HEAD
        if ($areCode === Area::AREA_ADMINHTML) {
=======
        if (in_array($areCode, [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $this->storeManager->expects(self::once())
                ->method('getStores')
                ->willReturn([$store]);
        } else {
            $this->storeManager->expects(self::once())
                ->method('getStore')
                ->willReturn($store);
        }

        $this->config->expects(self::once())
            ->method('getValue')
            ->with(
                self::identicalTo($path)
            )->willReturn('ARS,AUD,BZD');

        $result = $this->testSubject->getConfigCurrencies($path);

        self::assertEquals($expected, $result);
    }

    /**
     * Provide test data for getConfigCurrencies test.
     *
     * @return array
     */
    public function getConfigCurrenciesDataProvider()
    {
        return [
            ['areaCode' => Area::AREA_ADMINHTML],
<<<<<<< HEAD
=======
            ['areaCode' => Area::AREA_CRONTAB],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ['areaCode' => Area::AREA_FRONTEND],
        ];
    }
}
