<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productWebsiteFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productWebsite;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryIndexer;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    public function setUp()
    {
        $eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->productWebsiteFactory = $this->getMock(
            '\Magento\Catalog\Model\Product\WebsiteFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(
            '\Magento\Framework\Model\Resource\AbstractResource',
            ['updateAttributes', '_getWriteAdapter', '_getReadAdapter', '_construct', 'getIdFieldName'],
            [],
            '',
            false
        );
        $this->productWebsite = $this->getMock(
            '\Magento\Catalog\Model\Product\Website',
            ['addProducts', 'removeProducts', '__wakeup'],
            [],
            '',
            false
        );
        $this->productWebsiteFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->productWebsite));
        $this->categoryIndexer = $this->getMock(
            '\Magento\Indexer\Model\Indexer',
            ['getId', 'load', 'isScheduled', 'reindexList'],
            [],
            '',
            false
        );
        $this->eavConfig = $this->getMock(
            '\Magento\Eav\Model\Config',
            ['__wakeup', 'getAttribute'],
            [],
            '',
            false
        );
        $this->eavAttribute = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            ['__wakeup', 'isIndexable'],
            [],
            '',
            false
        );
        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Product\Action',
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
            ->will($this->returnSelf());

        $this->categoryIndexer
            ->expects($this->any())
            ->method('isScheduled')
            ->will($this->returnValue(false));
        $this->categoryIndexer
            ->expects($this->any())
            ->method('reindexList')
            ->will($this->returnValue($productIds));
        $this->prepareIndexer();
        $this->eavConfig
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->eavAttribute));
        $this->eavAttribute
            ->expects($this->any())
            ->method('isIndexable')
            ->will($this->returnValue(false));
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
            ->will($this->returnSelf());

        $this->categoryIndexer
            ->expects($this->any())
            ->method('isScheduled')
            ->will($this->returnValue(false));
        $this->categoryIndexer
            ->expects($this->any())
            ->method('reindexList')
            ->will($this->returnValue($productIds));
        $this->prepareIndexer();
        $this->model->updateWebsites($productIds, $websiteIds, $type);
        $this->assertEquals($this->model->getDataByKey('product_ids'), $productIdsUnique);
        $this->assertEquals($this->model->getDataByKey('website_ids'), $websiteIds);
        $this->assertEquals($this->model->getDataByKey('action_type'), $type);
    }

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
            ->will($this->returnValue($this->categoryIndexer));
    }
}
