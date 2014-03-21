<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Shipping\Model\Order;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $carrier = $this->getMock(
            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
            array('setStore', 'getTrackingInfo'),
            array(),
            '',
            false
        );
        $carrier->expects($this->once())->method('setStore')->with('');
        $carrier->expects($this->once())->method('getTrackingInfo')->will($this->returnValue('trackingInfo'));

        $carrierFactory = $this->getMock(
            '\Magento\Shipping\Model\CarrierFactory',
            array('create'),
            array(),
            '',
            false
        );
        $carrierFactory->expects($this->once())->method('create')->will($this->returnValue($carrier));

        $shipment = $this->getMock(
            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
            array('load'),
            array(),
            '',
            false
        );
        $shipment->expects($this->any())->method('load')->will($this->returnValue($shipment));

        $shipmentFactory = $this->getMock(
            '\Magento\Sales\Model\Order\ShipmentFactory',
            array('create'),
            array(),
            '',
            false
        );
        $shipmentFactory->expects($this->any())->method('create')->will($this->returnValue($shipment));

        /** @var \Magento\Shipping\Model\Order\Track $model */
        $model = $helper->getObject(
            'Magento\Shipping\Model\Order\Track',
            array('carrierFactory' => $carrierFactory, 'shipmentFactory' => $shipmentFactory)
        );

        $this->assertEquals('trackingInfo', $model->getNumberDetail());
    }
}
