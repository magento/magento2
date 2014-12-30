<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Helper\Product;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Product\Flat\Indexer'
        );
        $this->_state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Indexer\Product\Flat\State'
        );
    }

    public function testIsEnabledDefault()
    {
        $this->assertFalse($this->_state->isFlatEnabled());
    }

    /**
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testIsEnabled()
    {
        $this->assertTrue($this->_state->isFlatEnabled());
    }

    public function testIsAddFilterableAttributesDefault()
    {
        $this->assertEquals(0, $this->_helper->isAddFilterableAttributes());
    }

    public function testIsAddFilterableAttributes()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            ['addFilterableAttrs' => 1]
        );
        $this->assertEquals(1, $helper->isAddFilterableAttributes());
    }

    public function testIsAddChildDataDefault()
    {
        $this->assertEquals(0, $this->_helper->isAddChildData());
    }

    public function testIsAddChildData()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            ['addChildData' => 1]
        );
        $this->assertEquals(1, $helper->isAddChildData());
    }
}
