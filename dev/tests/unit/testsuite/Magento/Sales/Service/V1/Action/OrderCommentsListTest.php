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
 * Class OrderCommentsListTest
 */
class OrderCommentsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Action\OrderCommentsList
     */
    protected $orderCommentsList;

    /**
     * @var \Magento\Sales\Model\Order\Status\HistoryRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyRepositoryMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\OrderStatusHistoryMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyMapperMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaBuilderMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\OrderSearchResultsBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCommentMock;

    /**
     * @var \Magento\Sales\Service\V1\Data\OrderStatusHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    protected function setUp()
    {
        $this->historyRepositoryMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\HistoryRepository',
            ['find'],
            [],
            '',
            false
        );
        $this->historyMapperMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderStatusHistoryMapper',
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
            'Magento\Sales\Service\V1\Data\OrderStatusHistorySearchResultsBuilder',
            ['setItems', 'setSearchCriteria', 'create', 'setTotalCount'],
            [],
            '',
            false
        );
        $this->orderCommentMock = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
            [],
            [],
            '',
            false
        );
        $this->dataObjectMock = $this->getMock(
            'Magento\Sales\Service\V1\Data\OrderStatusHistory',
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
        $this->orderCommentsList = new OrderCommentsList(
            $this->historyRepositoryMock,
            $this->historyMapperMock,
            $this->criteriaBuilderMock,
            $this->filterBuilderMock,
            $this->searchResultsBuilderMock
        );

    }

    /**
     * test order comments list service
     */
    public function testInvoke()
    {
        $orderId = 1;
        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with($this->equalTo('parent_id'))
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($this->equalTo($orderId))
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

        $this->historyRepositoryMock->expects($this->once())
            ->method('find')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue([$this->orderCommentMock]));

        $this->historyMapperMock->expects($this->once())
            ->method('extractDto')
            ->with($this->equalTo($this->orderCommentMock))
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

        $this->assertEquals('expected-result', $this->orderCommentsList->invoke($orderId));
    }
}
