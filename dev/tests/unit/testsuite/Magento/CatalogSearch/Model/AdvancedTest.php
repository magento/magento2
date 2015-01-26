<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model;

class AdvancedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Attribute\Backend\Sku
     */
    protected $skuAttribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\Advanced\Collection
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\Advanced
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\Engine
     */
    protected $engine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\EngineProvider
     */
    protected $engineProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Resource\Eav\Attribute
     */
    protected $attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Collection
     */
    protected $dataCollection;

    protected function setUp()
    {
        $this->skuAttribute = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Backend\Sku',
            ['getTable'],
            [],
            '',
            false
        );
        $this->collection = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Advanced\Collection',
            [
                'addAttributeToSelect',
                'setStore',
                'addMinimalPrice',
                'addTaxPercents',
                'addStoreFilter',
                'setVisibility',
                'addFieldsToFilter'
            ],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Advanced',
            ['prepareCondition', '__wakeup', 'getIdFieldName'],
            [],
            '',
            false
        );
        $this->engine = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Engine',
            ['getResource', '__wakeup', 'getAdvancedResultCollection'],
            [],
            '',
            false
        );
        $this->engineProvider = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\EngineProvider',
            ['get'],
            [],
            '',
            false
        );
        $this->attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getAttributeCode', 'getStoreLabel', 'getFrontendInput', 'getBackend', 'getBackendType', '__wakeup'],
            [],
            '',
            false
        );
        $this->dataCollection = $this->getMock(
            'Magento\Framework\Data\Collection',
            ['getIterator'],
            [],
            '',
            false
        );
    }

    public function testAddFiltersVerifyAddConditionsToRegistry()
    {
        $registry = new \Magento\Framework\Registry();
        $values = ['sku' => 'simple'];
        $this->skuAttribute->expects($this->once())->method('getTable')
            ->will($this->returnValue('catalog_product_entity'));
        $this->collection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setStore')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addMinimalPrice')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addTaxPercents')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addStoreFilter')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setVisibility')->will($this->returnSelf());
        $this->resource->expects($this->any())->method('prepareCondition')
            ->will($this->returnValue(['like' => '%simple%']));
        $this->resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('entity_id'));
        $this->engine->expects($this->any())->method('getResource')->will($this->returnValue($this->resource));
        $this->engine->expects($this->any())->method('getAdvancedResultCollection')
            ->will($this->returnValue($this->collection));
        $this->engineProvider->expects($this->any())->method('get')->will($this->returnValue($this->engine));
        $this->attribute->expects($this->any())->method('getAttributeCode')->will($this->returnValue('sku'));
        $this->attribute->expects($this->any())->method('getStoreLabel')->will($this->returnValue('SKU'));
        $this->attribute->expects($this->any())->method('getFrontendInput')->will($this->returnValue('text'));
        $this->attribute->expects($this->any())->method('getBackend')->will($this->returnValue($this->skuAttribute));
        $this->attribute->expects($this->any())->method('getBackendType')->will($this->returnValue('static'));
        $this->dataCollection->expects($this->any())->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->attribute])));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\CatalogSearch\Model\Advanced $instance */
        $instance = $objectManager->getObject(
            'Magento\CatalogSearch\Model\Advanced',
            [
                'registry' => $registry,
                'engineProvider' => $this->engineProvider,
                'data' => ['attributes' => $this->dataCollection]
            ]
        );
        $instance->addFilters($values);
        $this->assertNotNull($registry->registry('advanced_search_conditions'));
    }
}
