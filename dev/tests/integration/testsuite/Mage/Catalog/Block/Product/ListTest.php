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
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Block_Product_List.
 *
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 */
class Mage_Catalog_Block_Product_ListTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Block_Product_List
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Catalog_Block_Product_List;
    }

    public function testGetLayer()
    {
        $this->assertInstanceOf('Mage_Catalog_Model_Layer', $this->_block->getLayer());
    }

    public function testGetLoadedProductCollection()
    {
        $this->_block->setShowRootCategory(true);
        $collection = $this->_block->getLoadedProductCollection();
        $this->assertInstanceOf(
            'Mage_Catalog_Model_Resource_Product_Collection',
            $collection
        );
        /* Check that root category was defined for Layer as current */
        $this->assertEquals(2, $this->_block->getLayer()->getCurrentCategory()->getId());
    }

    /**
     * @covers Mage_Catalog_Block_Product_List::getToolbarBlock
     * @covers Mage_Catalog_Block_Product_List::getMode
     * @covers Mage_Catalog_Block_Product_List::getToolbarHtml
     * @covers Mage_Catalog_Block_Product_List::toHtml
     */
    public function testToolbarCoverage()
    {
        $parent = $this->_getLayout()->createBlock('Mage_Catalog_Block_Product_List', 'parent');

        /* Prepare toolbar block */
        $toolbar = $parent->getToolbarBlock();
        $this->assertInstanceOf('Mage_Catalog_Block_Product_List_Toolbar', $toolbar, 'Default Toolbar');

        $parent->setChild('toolbar', $toolbar);
        /* In order to initialize toolbar collection block toHtml should be called before toolbar toHtml */
        $this->assertEmpty($parent->toHtml(), 'Block HTML'); /* Template not specified */
        $this->assertEquals('grid', $parent->getMode(), 'Default Mode'); /* default mode */
        $this->assertNotEmpty($parent->getToolbarHtml(), 'Toolbar HTML'); /* toolbar for one simple product */
    }


    public function testGetAdditionalHtmlEmpty()
    {
        $this->_block->setLayout($this->_getLayout());
        $this->assertEmpty($this->_block->getAdditionalHtml());
    }

    public function testGetAdditionalHtml()
    {
        $layout = $this->_getLayout();
        $parent = $layout->createBlock('Mage_Catalog_Block_Product_List');
        $childBlock = $layout->createBlock('Mage_Core_Block_Text', 'test', array('text' => 'test'));
        $layout->setChild($parent->getNameInLayout(), $childBlock->getNameInLayout(), 'additional');
        $this->assertEquals('test', $parent->getAdditionalHtml());
    }

    public function testSetCollection()
    {
        $this->_block->setCollection('test');
        $this->assertEquals('test', $this->_block->getLoadedProductCollection());
    }

    public function testGetPriceBlockTemplate()
    {
        $this->assertNull($this->_block->getPriceBlockTemplate());
        $this->_block->setData('price_block_template', 'test');
        $this->assertEquals('test', $this->_block->getPriceBlockTemplate());
    }

    public function testPrepareSortableFieldsByCategory()
    {
        $category = new Mage_Catalog_Model_Category();
        $category->setDefaultSortBy('name');
        $this->_block->prepareSortableFieldsByCategory($category);
        $this->assertEquals('name', $this->_block->getSortBy());
    }

    protected function _getLayout()
    {
        return Mage::app()->getLayout();
    }
}
