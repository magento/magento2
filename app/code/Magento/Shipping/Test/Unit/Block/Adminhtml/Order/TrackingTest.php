<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Block\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;
use Magento\Shipping\Model\Config;
use PHPUnit\Framework\TestCase;

class TrackingTest extends TestCase
{
    public function testLookup()
    {
        $shipment = new DataObject(['store_id' => 1]);

        $registry = $this->createPartialMock(Registry::class, ['registry']);
        $registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_shipment'
        )->willReturn(
            $shipment
        );

        $carrier = $this->createPartialMock(
            Freeshipping::class,
            ['isTrackingAvailable', 'getConfigData']
        );
        $carrier->expects($this->once())->method('isTrackingAvailable')->willReturn(true);
        $carrier->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            'title'
        )->willReturn(
            'configdata'
        );

        $config = $this->createPartialMock(Config::class, ['getAllCarriers']);
        $config->expects(
            $this->once()
        )->method(
            'getAllCarriers'
        )->with(
            1
        )->willReturn(
            ['free' => $carrier]
        );

        $model = $this->getMockBuilder(Tracking::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new \ReflectionClass(Tracking::class);

        $registryProperty = $reflection->getProperty('_coreRegistry');
        $registryProperty->setValue($model, $registry);

        $configProperty = $reflection->getProperty('_shippingConfig');
        $configProperty->setValue($model, $config);

        $this->assertEquals(['custom' => 'Custom Value', 'free' => 'configdata'], $model->getCarriers());
    }
}
