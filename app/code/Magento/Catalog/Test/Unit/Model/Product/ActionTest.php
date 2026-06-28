<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Website;
use Magento\Catalog\Model\Product\WebsiteFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActionTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Action
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $productWebsiteFactory;

    /**
     * @var MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $productWebsite;

    /**
     * @var MockObject
     */
    protected $categoryIndexer;

    /**
     * @var Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var Attribute|MockObject
     */
    protected $eavAttribute;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->productWebsiteFactory = $this->createPartialMock(
            WebsiteFactory::class,
            ['create']
        );
        $this->resource = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['setUpdateAttributesResult', 'getIdFieldName', 'updateAttributes', '_construct', 'getConnection']
        );
        $this->resource->method('setUpdateAttributesResult')->willReturnSelf();
        $this->resource->method('getIdFieldName')->willReturn('entity_id');
        $this->resource->method('updateAttributes')->willReturnSelf();
        $this->productWebsite = $this->createPartialMock(
            Website::class,
            ['addProducts', 'removeProducts']
        );
        $this->productWebsiteFactory
            ->method('create')->willReturn($this->productWebsite);
        $this->categoryIndexer = $this->createPartialMock(
            Indexer::class,
            ['getId', 'load', 'isScheduled', 'reindexList']
        );
        $this->eavConfig = $this->createPartialMock(Config::class, [ 'getAttribute']);
        $this->eavAttribute = $this->createPartialMock(
            Attribute::class,
            [ 'isIndexable']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Action::class,
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

        $this->categoryIndexer
            ->method('isScheduled')->willReturn(false);
        $this->categoryIndexer
            ->method('reindexList')->willReturn($productIds);
        $this->prepareIndexer();
        $this->eavConfig
            ->method('getAttribute')->willReturn($this->eavAttribute);
        $this->eavAttribute
            ->method('isIndexable')->willReturn(false);
        $this->assertEquals($this->model, $this->model->updateAttributes($productIds, $attrData, $storeId));
        $this->assertEquals($this->model->getDataByKey('product_ids'), $productIdsUnique);
        $this->assertEquals($this->model->getDataByKey('attributes_data'), $attrData);
        $this->assertEquals($this->model->getDataByKey('store_id'), $storeId);
    }

    /**
     * @param $type
     * @param $methodName
     */
    #[DataProvider('updateWebsitesDataProvider')]
    public function testUpdateWebsites($type, $methodName)
    {
        $productIds = [1, 2, 2, 4];
        $productIdsUnique = [0 => 1, 1 => 2, 3 => 4];
        $websiteIds = [1];
        $this->productWebsite
            ->expects($this->any())
            ->method($methodName)
            ->with($websiteIds, $productIds)->willReturnSelf();

        $this->categoryIndexer
            ->method('isScheduled')->willReturn(false);
        $this->categoryIndexer
            ->method('reindexList')->willReturn($productIds);
        $this->prepareIndexer();
        $this->model->updateWebsites($productIds, $websiteIds, $type);
        $this->assertEquals($this->model->getDataByKey('product_ids'), $productIdsUnique);
        $this->assertEquals($this->model->getDataByKey('website_ids'), $websiteIds);
        $this->assertEquals($this->model->getDataByKey('action_type'), $type);
    }

    /**
     * @return array
     */
    public static function updateWebsitesDataProvider()
    {
        return [
            ['type' => 'add', 'methodName' => 'addProducts'],
            ['type' => 'remove', 'methodName' => 'removeProducts']
        ];
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Category::INDEXER_ID)
            ->willReturn($this->categoryIndexer);
    }
}
