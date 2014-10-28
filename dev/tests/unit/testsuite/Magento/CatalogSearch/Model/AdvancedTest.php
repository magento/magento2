<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\CatalogSearch\Model\Resource\Fulltext\Engine
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
            array('getTable'),
            array(),
            '',
            false
        );
        $this->collection = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Advanced\Collection',
            array(
                'addAttributeToSelect',
                'setStore',
                'addMinimalPrice',
                'addTaxPercents',
                'addStoreFilter',
                'setVisibility',
                'addFieldsToFilter'
            ),
            array(),
            '',
            false
        );
        $this->resource = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Advanced',
            array('prepareCondition', '__wakeup', 'getIdFieldName'),
            array(),
            '',
            false
        );
        $this->engine = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\Fulltext\Engine',
            array('getResource', '__wakeup', 'getAdvancedResultCollection'),
            array(),
            '',
            false
        );
        $this->engineProvider = $this->getMock(
            'Magento\CatalogSearch\Model\Resource\EngineProvider',
            array('get'),
            array(),
            '',
            false
        );
        $this->attribute = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            array('getAttributeCode', 'getStoreLabel', 'getFrontendInput', 'getBackend', 'getBackendType', '__wakeup'),
            array(),
            '',
            false
        );
        $this->dataCollection = $this->getMock(
            'Magento\Framework\Data\Collection',
            array('getIterator'),
            array(),
            '',
            false
        );
    }

    public function testAddFiltersVerifyAddConditionsToRegistry()
    {
        $registry = new \Magento\Framework\Registry();
        $values = array('sku' => 'simple');
        $this->skuAttribute->expects($this->once())->method('getTable')
            ->will($this->returnValue('catalog_product_entity'));
        $this->collection->expects($this->any())->method('addAttributeToSelect')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setStore')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addMinimalPrice')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addTaxPercents')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('addStoreFilter')->will($this->returnSelf());
        $this->collection->expects($this->any())->method('setVisibility')->will($this->returnSelf());
        $this->resource->expects($this->any())->method('prepareCondition')
            ->will($this->returnValue(array('like' => '%simple%')));
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
            ->will($this->returnValue(new \ArrayIterator(array($this->attribute))));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\CatalogSearch\Model\Advanced $instance */
        $instance = $objectManager->getObject(
            'Magento\CatalogSearch\Model\Advanced',
            array(
                'registry' => $registry,
                'engineProvider' => $this->engineProvider,
                'data' => array('attributes' => $this->dataCollection)
            )
        );
        $instance->addFilters($values);
        $this->assertNotNull($registry->registry('advanced_search_conditions'));
    }
}
