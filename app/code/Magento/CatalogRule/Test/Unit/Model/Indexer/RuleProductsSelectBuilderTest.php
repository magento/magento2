<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Unit\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Store\Api\Data\WebsiteInterface;

class RuleProductsSelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeTableSwitcherMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeTableSwitcherMock =
            $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder(
            $this->resourceMock,
            $this->eavConfigMock,
            $this->storeManagerMock,
            $this->metadataPoolMock,
            $this->activeTableSwitcherMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuild()
    {
        $websiteId = 55;
        $ruleTable = 'catalogrule_product';
        $rplTable = 'catalogrule_product_replica';
        $prTable = 'catalog_product_entity';
        $wsTable = 'catalog_product_website';
        $productMock = $this->getMockBuilder(Product::class)->disableOriginalConstructor()->getMock();
        $productMock->expects($this->exactly(2))->method('getEntityId')->willReturn(500);

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)->disableOriginalConstructor()->getMock();
        $this->resourceMock->expects($this->at(0))->method('getConnection')->willReturn($connectionMock);

        $this->activeTableSwitcherMock->expects($this->once())
            ->method('getAdditionalTableName')
            ->with($ruleTable)
            ->willReturn($rplTable);

        $this->resourceMock->expects($this->at(1))->method('getTableName')->with($ruleTable)->willReturn($ruleTable);
        $this->resourceMock->expects($this->at(2))->method('getTableName')->with($rplTable)->willReturn($rplTable);
        $this->resourceMock->expects($this->at(3))->method('getTableName')->with($prTable)->willReturn($prTable);
        $this->resourceMock->expects($this->at(4))->method('getTableName')->with($wsTable)->willReturn($wsTable);

        $selectMock = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();
        $connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $selectMock->expects($this->at(0))->method('from')->with(['rp' => $rplTable])->willReturnSelf();
        $selectMock->expects($this->at(1))
            ->method('order')
            ->with(['rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id'])
            ->willReturnSelf();
        $selectMock->expects($this->at(2))->method('where')->with('rp.product_id=?', 500)->willReturnSelf();

        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)->disableOriginalConstructor()->getMock();
        $this->eavConfigMock->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, 'price')
            ->willReturn($attributeMock);
        $backendMock = $this->getMockBuilder(AbstractBackend::class)->disableOriginalConstructor()->getMock();
        $backendMock->expects($this->once())->method('getTable')->willReturn('price_table');
        $attributeMock->expects($this->once())->method('getBackend')->willReturn($backendMock);
        $attributeMock->expects($this->once())->method('getId')->willReturn(200);

        $metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)->disableOriginalConstructor()->getMock();
        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn('link_field');

        $selectMock->expects($this->at(3))
            ->method('join')
            ->with(['e' => $prTable], 'e.entity_id = rp.product_id', [])
            ->willReturnSelf();
        $selectMock->expects($this->at(4))
            ->method('join')
            ->with(
                ['pp_default' => 'price_table'],
                'pp_default.link_field=e.link_field AND (pp_default.attribute_id=200) and pp_default.store_id=0',
                []
            )->willReturnSelf();
        $websiteMock = $this->getMockBuilder(WebsiteInterface::class)
            ->setMethods(['getDefaultGroup'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($websiteId)
            ->willReturn($websiteMock);

        $groupMock = $this->getMockBuilder(\Magento\Store\Model\Group::class)
            ->setMethods(['getDefaultStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->once())->method('getDefaultGroup')->willReturn($groupMock);
        $groupMock->expects($this->once())->method('getDefaultStoreId')->willReturn(700);

        $selectMock->expects($this->at(5))
            ->method('joinInner')
            ->with(
                ['product_website' => $wsTable],
                'product_website.product_id=rp.product_id '
                . 'AND product_website.website_id = rp.website_id '
                . 'AND product_website.website_id='
                . $websiteId,
                []
            )->willReturnSelf();
        $selectMock->expects($this->at(6))
            ->method('joinLeft')
            ->with(
                ['pp' . $websiteId => 'price_table'],
                'pp55.link_field=e.link_field AND (pp55.attribute_id=200) and pp55.store_id=700',
                []
            )->willReturnSelf();

        $connectionMock->expects($this->once())
            ->method('getIfNullSql')
            ->with('pp55.value', 'pp_default.value')
            ->willReturn('IF NULL SQL');
        $selectMock->expects($this->at(7))
            ->method('columns')
            ->with(['default_price' => 'IF NULL SQL'])
            ->willReturnSelf();
        $statementMock = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->expects($this->once())->method('query')->with($selectMock)->willReturn($statementMock);

        $this->assertEquals($statementMock, $this->model->build($websiteId, $productMock, true));
    }
}
