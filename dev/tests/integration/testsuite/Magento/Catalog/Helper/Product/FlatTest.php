<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
