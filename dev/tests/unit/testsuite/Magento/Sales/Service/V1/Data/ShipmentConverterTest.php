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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class ShipmentConverterTest
 * @package Magento\Sales\Service\V1\Data
 */
class ShipmentConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\ShipmentConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->shipmentLoaderMock = $this->getMockBuilder('Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->converter = new \Magento\Sales\Service\V1\Data\ShipmentConverter($this->shipmentLoaderMock);
    }

    public function testGetModel()
    {
        $orderId = 1;
        $shipmentId = 2;
        $items = [];
        $tracking = [];

        $shipmentDataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $shipmentDataObjectMock->expects($this->any())
            ->method('getOrderId')
            ->will($this->returnValue($orderId));
        $shipmentDataObjectMock->expects($this->any())
            ->method('getEntityId')
            ->will($this->returnValue($shipmentId));
        $shipmentDataObjectMock->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($items));
        $shipmentDataObjectMock->expects($this->any())
            ->method('getTracks')
            ->will($this->returnValue($tracking));

        $shipmentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->with()
            ->will($this->returnValue($shipmentMock));

        $this->assertInstanceOf(
            'Magento\Sales\Model\Order\Shipment',
            $this->converter->getModel($shipmentDataObjectMock)
        );
    }
}
