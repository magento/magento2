<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Block\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Carrier\Freeshipping;
use Magento\Shipping\Block\Adminhtml\Order\Tracking;
use Magento\Shipping\Model\Config;
use PHPUnit\Framework\TestCase;

class TrackingTest extends TestCase
{
    public function testLookup()
    {
        $helper = new ObjectManager($this);

        $shipment = new DataObject(['store_id' => 1]);

        $registry = $this->createPartialMock(Registry::class, ['registry']);
        $registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_shipment'
        )->will(
            $this->returnValue($shipment)
        );

        $carrier = $this->createPartialMock(
            Freeshipping::class,
            ['isTrackingAvailable', 'getConfigData']
        );
        $carrier->expects($this->once())->method('isTrackingAvailable')->will($this->returnValue(true));
        $carrier->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            'title'
        )->will(
            $this->returnValue('configdata')
        );

        $config = $this->createPartialMock(Config::class, ['getAllCarriers']);
        $config->expects(
            $this->once()
        )->method(
            'getAllCarriers'
        )->with(
            1
        )->will(
            $this->returnValue(['free' => $carrier])
        );

        /** @var Tracking $model */
        $model = $helper->getObject(
            Tracking::class,
            ['registry' => $registry, 'shippingConfig' => $config]
        );

        $this->assertEquals(['custom' => 'Custom Value', 'free' => 'configdata'], $model->getCarriers());
    }
}
