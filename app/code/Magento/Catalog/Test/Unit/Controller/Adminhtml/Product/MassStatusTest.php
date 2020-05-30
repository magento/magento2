<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Controller\Adminhtml\Product\MassStatus;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTest;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatusTest extends ProductTest
{
    /**
     * @var Processor|MockObject
     */
    private $priceProcessorMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Filter|MockObject
     */
    private $filterMock;

    /**
     * @var Builder|MockObject
     */
    private $productBuilderMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $abstractDbMock;

    /**
     * @var Action|MockObject
     */
    private $actionMock;

    protected function setUp(): void
    {
        $this->priceProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productBuilderMock = $this->getMockBuilder(Builder::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getStoreId', '__sleep'])
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

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds', 'getResource'])
            ->getMock();
        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();
        $this->actionMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->abstractDbMock);

        $additionalParams = [
            'resultFactory' => $resultFactory
        ];
        /** @var Context $context */
        $context = $this->initContext($additionalParams);

        $this->action = new MassStatus(
            $context,
            $this->productBuilderMock,
            $this->priceProcessorMock,
            $this->filterMock,
            $collectionFactoryMock,
            $this->actionMock
        );
    }

    public function testMassStatusAction()
    {
        $storeId = 2;
        $status = Status::STATUS_DISABLED;
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
            ->willReturnMap(
                [
                    ['store', null, $storeId],
                    ['status', null, $status],
                    ['filters', [], $filters]
                ]
            );
        $this->actionMock->expects($this->once())
            ->method('updateAttributes')
            ->with([3], ['status' => $status], 2);
        $this->priceProcessorMock->expects($this->once())
            ->method('reindexList');

        $this->action->execute();
    }
}
