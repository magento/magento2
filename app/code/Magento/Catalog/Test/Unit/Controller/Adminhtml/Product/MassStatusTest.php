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
use Magento\Catalog\Test\Unit\Controller\Adminhtml\ProductTestCase;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatusTest extends ProductTestCase
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

    /**
     * @var AttributeHelper|MockObject
     */
    private $attributeHelperMock;

    protected function setUp(): void
    {
        $this->priceProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productBuilderMock = $this->getMockBuilder(Builder::class)
            ->onlyMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeId', 'getStoreId', '__sleep'])
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
            ->onlyMethods(['create'])
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds', 'getResource'])
            ->getMock();
        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection'])
            ->getMock();
        $this->actionMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeHelperMock = $this->getMockBuilder(AttributeHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setProductIds'])
            ->getMock();

        $collectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
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
            $this->actionMock,
            $this->attributeHelperMock
        );
    }

    public function testMassStatusAction()
    {
        $storeId = 2;
        $status = Status::STATUS_DISABLED;
        $filters = [
            'store_id' => 2,
        ];
        $productIds = [3];

        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->abstractDbMock);
        $this->abstractDbMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($productIds);
        $this->request->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['store', null, $storeId],
                    ['status', null, $status],
                    ['filters', [], $filters]
                ]
            );
        $this->attributeHelperMock->expects($this->once())
            ->method('setProductIds')
            ->with($productIds);
        $this->actionMock->expects($this->once())
            ->method('updateAttributes')
            ->with($productIds, ['status' => $status], 2);
        $this->priceProcessorMock->expects($this->once())
            ->method('reindexList');

        $this->action->execute();
    }
}
