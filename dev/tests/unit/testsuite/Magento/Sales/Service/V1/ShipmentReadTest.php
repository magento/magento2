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
 * Class ShipmentReadTest
 */
class ShipmentReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentGet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentGetMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentListMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentCommentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentCommentsListMock;

    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentLabelGet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLabelGetMock;

    /**
     * @var \Magento\Sales\Service\V1\ShipmentRead
     */
    protected $shipmentRead;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->shipmentGetMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentGet',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentListMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentList',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentCommentsListMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentCommentsList',
            ['invoke'],
            [],
            '',
            false
        );
        $this->shipmentLabelGetMock = $this->getMock(
            'Magento\Sales\Service\V1\Action\ShipmentLabelGet',
            ['invoke'],
            [],
            '',
            false
        );

        $this->shipmentRead = new ShipmentRead(
            $this->shipmentGetMock,
            $this->shipmentListMock,
            $this->shipmentCommentsListMock,
            $this->shipmentLabelGetMock
        );
    }

    /**
     * test shipment get
     */
    public function testGet()
    {
        $this->shipmentGetMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('shipment-do'));
        $this->assertEquals('shipment-do', $this->shipmentRead->get(1));
    }

    /**
     * test shipment list
     */
    public function testSearch()
    {
        $searchCriteria = $this->getMock('Magento\Framework\Service\V1\Data\SearchCriteria', [], [], '', false);
        $this->shipmentListMock->expects($this->once())
            ->method('invoke')
            ->with($searchCriteria)
            ->will($this->returnValue('search_result'));
        $this->assertEquals('search_result', $this->shipmentRead->search($searchCriteria));
    }

    /**
     * test shipment comments list
     */
    public function testCommentsList()
    {
        $this->shipmentCommentsListMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('search_result'));
        $this->assertEquals('search_result', $this->shipmentRead->commentsList(1));
    }

    /**
     * test shipment label get
     */
    public function testGetLabel()
    {
        $this->shipmentLabelGetMock->expects($this->once())
            ->method('invoke')
            ->with(1)
            ->will($this->returnValue('shipment-do'));
        $this->assertEquals('shipment-do', $this->shipmentRead->getLabel(1));
    }
}
