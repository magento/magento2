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
 * Class ShipmentListTest
 */
class ShipmentListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentList
     */
    protected $shipmentList;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\ShipmentMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMapperMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\ShipmentSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->shipmentRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\ShipmentRepository',
            ['find'],
            [],
            '',
            false
        );
        $this->shipmentMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\ShipmentMapper',
            [],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\ShipmentSearchResultsBuilder',
            ['setItems', 'setSearchCriteria', 'create', 'setTotalCount'],
            [],
            '',
            false
        );
        $this->searchCriteriaMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\SearchCriteria',
            [],
            [],
            '',
            false
        );
        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Shipment',
            [],
            [],
            '',
            false
        );
        $this->shipmentList = new ShipmentList(
            $this->shipmentRepositoryMock,
            $this->shipmentMapperMock,
            $this->searchResultsBuilderMock
        );
    }

    /**
     * test shipment list service
     */
    public function testInvoke()
    {
        $this->shipmentRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue([$this->shipmentMock]));
        $this->shipmentMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->shipmentMock))
            ->will($this->returnValue($this->dataObjectMock));
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo([$this->dataObjectMock]))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setTotalCount')
            ->with($this->equalTo(count($this->shipmentMock)))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('expected-result'));
        $this->assertEquals('expected-result', $this->shipmentList->invoke($this->searchCriteriaMock));
    }
}
