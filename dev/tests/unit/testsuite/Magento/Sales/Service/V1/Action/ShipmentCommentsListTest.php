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
 * Class ShipmentCommentsListTest
 */
class ShipmentCommentsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\ShipmentCommentsList
     */
    protected $shipmentCommentsList;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\CommentRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\CommentMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentMapperMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\CommentSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentCommentMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    protected function setUp()
    {
        $this->commentRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\CommentRepository',
            ['find'],
            [],
            '',
            false
        );
        $this->commentMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CommentMapper',
            ['extractDto'],
            [],
            '',
            false
        );
        $this->criteriaBuilderMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['create', 'addFilter'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Service\V1\Data\FilterBuilder',
            ['setField', 'setValue', 'create'],
            [],
            '',
            false
        );
        $this->searchResultsBuilderMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\CommentSearchResultsBuilder',
            ['setItems', 'setSearchCriteria', 'create', 'setTotalCount'],
            [],
            '',
            false
        );
        $this->shipmentCommentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment\Comment',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\Comment',
            [],
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
        $this->shipmentCommentsList = new ShipmentCommentsList(
            $this->commentRepositoryMock,
            $this->commentMapperMock,
            $this->criteriaBuilderMock,
            $this->filterBuilderMock,
            $this->searchResultsBuilderMock
        );

    }

    /**
     * test shipment comments list service
     */
    public function testInvoke()
    {
        $shipmentId = 1;
        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with($this->equalTo('parent_id'))
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($this->equalTo($shipmentId))
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('filter'));
        $this->criteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with($this->equalTo(['eq' => 'filter']))
            ->will($this->returnSelf());
        $this->criteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->searchCriteriaMock));

        $this->commentRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue([$this->shipmentCommentMock]));

        $this->commentMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->shipmentCommentMock))
            ->will($this->returnValue($this->dataObjectMock));

        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo([$this->dataObjectMock]))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setTotalCount')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnSelf());
        $this->searchResultsBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue('expected-result'));

        $this->assertEquals('expected-result', $this->shipmentCommentsList->invoke($shipmentId));
    }
}
