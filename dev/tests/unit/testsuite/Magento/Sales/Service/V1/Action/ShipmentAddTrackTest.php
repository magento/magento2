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
namespace Magento\Sales\Service\V1\Action;

/**
 * Class ShipmentAddTrackTest
 */
class ShipmentAddTrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentAddTrack
     */
    protected $shipmentAddTrack;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackConverterMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataModelMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\ShipmentTrack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->trackConverterMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\TrackConverter',
            ['getModel'],
            [],
            '',
            false
        );
        $this->dataModelMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Track',
            ['save', '__wakeup'],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\ShipmentTrack',
            [],
            [],
            '',
            false
        );
        $this->shipmentAddTrack = new ShipmentAddTrack($this->trackConverterMock);
    }

    /**
     * Test shipment add track service
     */
    public function testInvoke()
    {
        $this->trackConverterMock->expects($this->once())
            ->method('getModel')
            ->with($this->equalTo($this->dataObjectMock))
            ->will($this->returnValue($this->dataModelMock));
        $this->dataModelMock->expects($this->once())
            ->method('save');
        $this->assertTrue($this->shipmentAddTrack->invoke($this->dataObjectMock));
    }
}
