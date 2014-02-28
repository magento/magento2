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

namespace Magento\Shipping\Block\Adminhtml\Order;

class TrackingTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $shipment = new \Magento\Object(['store_id' => 1]);

        $registry = $this->getMock('Magento\Registry', ['registry'], [], '', false);
        $registry->expects($this->once())->method('registry')
            ->with('current_shipment')->will($this->returnValue($shipment));

        $carrier = $this->getMock('Magento\Shipping\Model\Carrier\Freeshipping',
            ['isTrackingAvailable', 'getConfigData'], [], '', false);
        $carrier->expects($this->once())->method('isTrackingAvailable')->will($this->returnValue(true));
        $carrier->expects($this->once())->method('getConfigData')->with('title')
            ->will($this->returnValue('configdata'));

        $config = $this->getMock('Magento\Shipping\Model\Config', ['getAllCarriers'], [], '', false);
        $config->expects($this->once())->method('getAllCarriers')
            ->with(1)->will($this->returnValue(['free' => $carrier]));

        /** @var \Magento\Shipping\Block\Adminhtml\Order\Tracking $model */
        $model = $helper->getObject(
            'Magento\Shipping\Block\Adminhtml\Order\Tracking',
            [
                'registry' => $registry,
                'shippingConfig' => $config
            ]
        );

        $this->assertEquals([
            'custom' => 'Custom Value',
            'free' => 'configdata'
        ], $model->getCarriers());
    }
}
