<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product;

use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Website;
use Magento\Catalog\Model\Product\WebsiteFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ActionTest extends TestCase
{
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
        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->productWebsiteFactory = $this->createPartialMock(
            WebsiteFactory::class,
            ['create']
        );
        $this->resource = $this->getMockBuilder(AbstractResource::class)
            ->addMethods(['updateAttributes', 'getIdFieldName'])
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productWebsite = $this->createPartialMock(
            Website::class,
            ['addProducts', 'removeProducts']
        );
        $this->productWebsiteFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->productWebsite);
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
        $this->resource
            ->expects($this->any())
            ->method('updateAttributes')
            ->with($productIds, $attrData, $storeId)->willReturnSelf();

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
            ->with($websiteIds, $productIds)->willReturnSelf();

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
