<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Unit\Model;

use Magento\Config\App\Config\Type\System;
use Magento\Directory\Model\CurrencySystemConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CurrencySystemConfig model.
 */
class CurrencyConfigTest extends TestCase
{
    /**
     * @var CurrencySystemConfig
     */
    private $testSubject;

    /**
     * @var System|\PHPUnit_Framework_MockObject_MockObject
     */
    private $systemConfig;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->systemConfig = $this->getMockBuilder(System::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStores', 'getWebsites'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->testSubject = $objectManager->getObject(
            CurrencySystemConfig::class,
            [
                'systemConfig' => $this->systemConfig,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetConfigCurrencies()
    {
        $path = 'test/path';
        $expected = [
            0 => 'ARS',
            1 => 'AUD',
            3 => 'BZD',
            4 => 'CAD',
            5 => 'CLP',
            6 => 'EUR',
            7 => 'USD',
        ];

        /** @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects(self::once())
            ->method('getId')
            ->willReturn(1);

        /** @var WebsiteInterface|\PHPUnit_Framework_MockObject_MockObject $website */
        $website = $this->getMockBuilder(WebsiteInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $website->expects(self::once())
            ->method('getId')
            ->willReturn(1);

        $this->systemConfig->expects(self::exactly(3))
            ->method('get')
            ->withConsecutive(
                self::identicalTo('default/test/path'),
                self::identicalTo('websites/1/test/path'),
                self::identicalTo('stores/1/test/path')
            )->willReturnOnConsecutiveCalls(
                'USD,EUR',
                'AUD,ARS',
                'BZD,CAD,AUD,CLP'
            );

        $this->storeManager->expects(self::once())
            ->method('getStores')
            ->willReturn([$store]);
        $this->storeManager->expects(self::once())
            ->method('getWebsites')
            ->willReturn([$website]);

        $result = $this->testSubject->getConfigCurrencies($path);

        self::assertEquals($expected, $result);
    }
}
