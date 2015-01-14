<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CreditmemoServiceTest
 */
class CreditmemoServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Sales\Api\CreditmemoCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoCommentRepositoryMock;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoNotifierMock;

    /**
     * @var \Magento\Sales\Model\Service\CreditmemoService
     */
    protected $creditmemoService;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->creditmemoRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->creditmemoCommentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\CreditmemoCommentRepositoryInterface',
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            ['create', 'addFilter'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            ['setField', 'setValue', 'create'],
            [],
            '',
            false
        );
        $this->creditmemoNotifierMock = $this->getMock(
            'Magento\Sales\Model\Order\CreditmemoNotifier',
            [],
            [],
            '',
            false
        );

        $this->creditmemoService = $objectManager->getObject(
            'Magento\Sales\Model\Service\CreditmemoService',
            [
                'creditmemoRepository' => $this->creditmemoRepositoryMock,
                'creditmemoCommentRepository' => $this->creditmemoCommentRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'creditmemoNotifier' => $this->creditmemoNotifierMock
            ]
        );
    }

    /**
     * Run test cancel method
     */
    public function testCancel()
    {
        $id = 10;
        $creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['cancel'],
            [],
            '',
            false
        );
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($creditmemoMock));
        $creditmemoMock->expects($this->once())
            ->method('cancel')
            ->will($this->returnValue(true));

        $this->assertTrue($this->creditmemoService->cancel($id));
    }

    /**
     * Run test getCommentsList method
     */
    public function testGetCommentsList()
    {
        $id = 25;
        $returnValue = 'return-value';

        $filterMock = $this->getMock(
            'Magento\Framework\Api\Filter',
            [],
            [],
            '',
            false
        );
        $searchCriteriaMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteria',
            [],
            [],
            '',
            false
        );

        $this->filterBuilderMock->expects($this->once())
            ->method('setField')
            ->with('parent_id')
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('setValue')
            ->with($id)
            ->will($this->returnSelf());
        $this->filterBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($filterMock));
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(['eq' => $filterMock]);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->creditmemoCommentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->getCommentsList($id));
    }

    /**
     * Run test notify method
     */
    public function testNotify()
    {
        $id = 123;
        $returnValue = 'return-value';

        $modelMock = $this->getMockForAbstractClass(
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false
        );

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($modelMock));
        $this->creditmemoNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
        ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->creditmemoService->notify($id));
    }
}
