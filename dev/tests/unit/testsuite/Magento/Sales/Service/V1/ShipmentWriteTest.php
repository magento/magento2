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
namespace Magento\Sales\Service\V1;

/**
 * Class ShipmentWriteTest
 */
class ShipmentWriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentAddTrack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentAddTrackMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentRemoveTrack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentRemoveTrackMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentEmail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentEmailMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentAddComment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentAddCommentMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentCreate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentCreateMock;

    /**
     * @var \Magento\Sales\Service\V1\ShipmentWrite
     */
    protected $shipmentWrite;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->shipmentAddTrackMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentAddTrack',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentRemoveTrackMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentRemoveTrack',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentEmailMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentEmail',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentAddCommentMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentAddComment',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentCreateMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentCreate',
            ['invoke'],
            [],
            '',
            false
        );

        $this->shipmentWrite = new ShipmentWrite(
            $this->shipmentAddTrackMock,
            $this->shipmentRemoveTrackMock,
            $this->shipmentEmailMock,
            $this->shipmentAddCommentMock,
            $this->shipmentCreateMock
        );
    }

    /**
     * test shipment add comment
     */
    public function testAddTrack()
    {
        $track = $this->getMock('Magento\Sales\Service\V1\Data\ShipmentTrack', [], [], '', false);
        $this->shipmentAddTrackMock->expects($this->once())
            ->method('invoke')
            ->with($track)
            ->will($this->returnValue(true));
        $this->assertTrue($this->shipmentWrite->addTrack($track));
    }

    /**
     * test shipment removeTrack
     */
    public function testRemoveTrack()
    {
        $track = $this->getMock('Magento\Sales\Service\V1\Data\ShipmentTrack', [], [], '', false);
        $this->shipmentRemoveTrackMock->expects($this->once())
            ->method('invoke')
            ->with($track)
            ->will($this->returnValue(true));
        $this->assertTrue($this->shipmentWrite->removeTrack($track));
    }

    /**
     * test shipment email
     */
    public function testEmail()
    {
        $this->shipmentEmailMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue(true));
        $this->assertTrue($this->shipmentWrite->email(1));
    }

    /**
     * test shipment addComment
     */
    public function testAddComment()
    {
        $comment = $this->getMock('Magento\Sales\Service\V1\Data\Comment', [], [], '', false);
        $this->shipmentAddCommentMock->expects($this->once())
            ->method('invoke')
            ->with($comment)
            ->will($this->returnValue(true));
        $this->assertTrue($this->shipmentWrite->addComment($comment));
    }

    /**
     * test shipment create
     */
    public function testCreate()
    {
        $shipmentDataObject = $this->getMock('Magento\Sales\Service\V1\Data\Shipment', [], [], '', false);
        $this->shipmentCreateMock->expects($this->once())
            ->method('invoke')
            ->with($shipmentDataObject)
            ->will($this->returnValue(true));
        $this->assertTrue($this->shipmentWrite->create($shipmentDataObject));
    }
}
