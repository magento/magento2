<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Catalog\Model\Product\Action;

/**
 * Class MassStatusTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatusTest extends \Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest
{
    /**
     * @var Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceProcessorMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilderMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractDbMock;

    /**
     * @var Action|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionMock;

    protected function setUp()
    {
        $this->priceProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()->getMock();
        $this->productBuilderMock = $this->getMockBuilder(Builder::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep', '__wakeup'])
            ->getMock();
        $productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');
        $productMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn('1');
        $this->productBuilderMock->expects($this->any())
            ->method('build')
            ->willReturn($productMock);

        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds', 'getResource'])
            ->getMock();
        $this->filterMock = $this->getMockBuilder(\Magento\Ui\Component\MassAction\Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();
        $this->actionMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactoryMock =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->abstractDbMock);

        $additionalParams = [
            'resultFactory' => $resultFactory
        ];
        /** @var \Magento\Backend\App\Action\Context $context */
        $context = $this->initContext($additionalParams, [[Action::class, $this->actionMock]]);

        $this->action = new \Magento\Catalog\Controller\Adminhtml\Product\MassStatus(
            $context,
            $this->productBuilderMock,
            $this->priceProcessorMock,
            $this->filterMock,
            $collectionFactoryMock
        );
    }

    public function testMassStatusAction()
    {
        $storeId = 2;
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        $filters = [
            'store_id' => 2,
        ];

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->abstractDbMock);
        $this->abstractDbMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([3]);
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap([
                ['store', null, $storeId],
                ['status', null, $status],
                ['filters', [], $filters]
            ]);
        $this->actionMock->expects($this->once())
            ->method('updateAttributes')
            ->with([3], ['status' => $status], 2);
        $this->priceProcessorMock->expects($this->once())
            ->method('reindexList');

        $this->action->execute();
    }
}
