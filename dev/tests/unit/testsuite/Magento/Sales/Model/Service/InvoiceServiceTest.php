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
namespace Magento\Sales\Model\Service;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class InvoiceServiceTest
 */
class InvoiceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentRepositoryMock;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $criteriaBuilderMock;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * Invoice Notifier
     *
     * @var \Magento\Sales\Model\Order\InvoiceNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceNotifierMock;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->repositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\InvoiceRepositoryInterface',
            ['get'],
            '',
            false
        );
        $this->commentRepositoryMock = $this->getMockForAbstractClass(
            'Magento\Sales\Api\InvoiceCommentRepositoryInterface',
            ['getList'],
            '',
            false
        );
        $this->criteriaBuilderMock = $this->getMock(
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
        $this->invoiceNotifierMock = $this->getMock(
            'Magento\Sales\Model\Order\InvoiceNotifier',
            ['notify'],
            [],
            '',
            false
        );

        $this->invoiceService = $objectManager->getObject(
            'Magento\Sales\Model\Service\InvoiceService',
            [
                'repository' => $this->repositoryMock,
                'commentRepository' => $this->commentRepositoryMock,
                'criteriaBuilder' => $this->criteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock,
                'notifier' => $this->invoiceNotifierMock
            ]
        );
    }

    /**
     * Run test setCapture method
     */
    public function testSetCapture()
    {
        $id = 145;
        $returnValue = true;

        $invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['capture'],
            [],
            '',
            false
        );

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($invoiceMock));
        $invoiceMock->expects($this->once())
            ->method('capture')
            ->will($this->returnValue($returnValue));

        $this->assertTrue($this->invoiceService->setCapture($id));
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
        $this->criteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with(['eq' => $filterMock]);
        $this->criteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->commentRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->invoiceService->getCommentsList($id));
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

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($modelMock));
        $this->invoiceNotifierMock->expects($this->once())
            ->method('notify')
            ->with($modelMock)
            ->will($this->returnValue($returnValue));

        $this->assertEquals($returnValue, $this->invoiceService->notify($id));
    }

    /**
     * Run test setVoid method
     */
    public function testSetVoid()
    {
        $id = 145;
        $returnValue = true;

        $invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['void'],
            [],
            '',
            false
        );

        $this->repositoryMock->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($invoiceMock));
        $invoiceMock->expects($this->once())
            ->method('void')
            ->will($this->returnValue($returnValue));

        $this->assertTrue($this->invoiceService->setVoid($id));
    }
}
