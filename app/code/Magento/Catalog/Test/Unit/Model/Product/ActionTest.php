<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

class ActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productWebsiteFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productWebsite;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryIndexer;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->productWebsiteFactory = $this->createPartialMock(
            \Magento\Catalog\Model\Product\WebsiteFactory::class,
            ['create']
        );
        $this->resource = $this->createPartialMock(
            \Magento\Framework\Model\ResourceModel\AbstractResource::class,
            [
                'updateAttributes',
                'getConnection',
                '_construct',
                'getIdFieldName',
            ]
        );
        $this->productWebsite = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Website::class,
            ['addProducts', 'removeProducts', '__wakeup']
        );
        $this->productWebsiteFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->productWebsite);
        $this->categoryIndexer = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer::class,
            ['getId', 'load', 'isScheduled', 'reindexList']
        );
        $this->eavConfig = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['__wakeup', 'getAttribute']);
        $this->eavAttribute = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['__wakeup', 'isIndexable']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Action::class,
            [
                'eventDispatcher' => $eventManagerMock,
                'resource' => $this->resource,
                'productWebsiteFactory' => $this->productWebsiteFactory,
                'indexerRegistry' => $this->indexerRegistryMock,
                'eavConfig' => $this->eavConfig
            ]
        );
    }

    public function testUpdateAttributes()
    {
        $productIds = [1, 2, 2, 4];
        $productIdsUnique = [0 => 1, 1 => 2, 3 => 4];
        $attrData = [1];
        $storeId = 1;
        $this->resource
            ->expects($this->any())
            ->method('updateAttributes')
            ->with($productIds, $attrData, $storeId)
            ->willReturnSelf();

        $this->categoryIndexer
            ->expects($this->any())
            ->method('isScheduled')
            ->willReturn(false);
        $this->categoryIndexer
            ->expects($this->any())
            ->method('reindexList')
            ->willReturn($productIds);
        $this->prepareIndexer();
        $this->eavConfig
            ->expects($this->any())
            ->method('getAttribute')
            ->willReturn($this->eavAttribute);
        $this->eavAttribute
            ->expects($this->any())
            ->method('isIndexable')
            ->willReturn(false);
        $this->assertEquals($this->model, $this->model->updateAttributes($productIds, $attrData, $storeId));
        $this->assertEquals($this->model->getDataByKey('product_ids'), $productIdsUnique);
        $this->assertEquals($this->model->getDataByKey('attributes_data'), $attrData);
        $this->assertEquals($this->model->getDataByKey('store_id'), $storeId);
    }

    /**
     * @param $type
     * @param $methodName
     * @dataProvider updateWebsitesDataProvider
     */
    public function testUpdateWebsites($type, $methodName)
    {
        $productIds = [1, 2, 2, 4];
        $productIdsUnique = [0 => 1, 1 => 2, 3 => 4];
        $websiteIds = [1];
        $this->productWebsite
            ->expects($this->any())
            ->method($methodName)
            ->with($websiteIds, $productIds)
            ->willReturnSelf();

        $this->categoryIndexer
            ->expects($this->any())
            ->method('isScheduled')
            ->willReturn(false);
        $this->categoryIndexer
            ->expects($this->any())
            ->method('reindexList')
            ->willReturn($productIds);
        $this->prepareIndexer();
        $this->model->updateWebsites($productIds, $websiteIds, $type);
        $this->assertEquals($this->model->getDataByKey('product_ids'), $productIdsUnique);
        $this->assertEquals($this->model->getDataByKey('website_ids'), $websiteIds);
        $this->assertEquals($this->model->getDataByKey('action_type'), $type);
    }

    /**
     * @return array
     */
    public function updateWebsitesDataProvider()
    {
        return [
            ['$type' => 'add', '$methodName' => 'addProducts'],
            ['$type' => 'remove', '$methodName' => 'removeProducts']
        ];
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
            ->willReturn($this->categoryIndexer);
    }
}
