<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
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
     * @var ProductCollection|MockObject
     */
    private $productCollectionMock;

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
        $this->priceProcessorMock = $this->createMock(Processor::class);
        $this->productBuilderMock = $this->createPartialMock(Builder::class, ['build']);

        $productMock = $this->createPartialMock(Product::class, ['getTypeId', 'getStoreId', '__sleep']);
        $productMock->method('getTypeId')->willReturn('simple');
        $productMock->method('getStoreId')->willReturn('1');
        $this->productBuilderMock->method('build')->willReturn($productMock);

        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirectMock);

        $this->productCollectionMock = $this->createPartialMock(
            ProductCollection::class,
            ['getAllIds', 'getResource']
        );
        $this->filterMock = $this->createPartialMock(Filter::class, ['getCollection']);
        $this->actionMock = $this->createMock(Action::class);
        $this->attributeHelperMock = $this->createPartialMock(AttributeHelper::class, ['setProductIds']);

        $collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $collectionFactoryMock->method('create')->willReturn($this->productCollectionMock);

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
            ->willReturn($this->productCollectionMock);
        $this->productCollectionMock->expects($this->once())
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
