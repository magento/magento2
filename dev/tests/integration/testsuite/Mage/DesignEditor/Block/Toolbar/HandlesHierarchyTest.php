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
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Block_Toolbar_HandlesHierarchyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Block_Toolbar_HandlesHierarchy
     */
    protected $_block;

    protected function setUp()
    {
        $layoutUtility = new Mage_Core_Utility_Layout($this);
        $pageTypesFixture = __DIR__ . '/../../../Core/Model/Layout/_files/_handles.xml';
        $this->_block = new Mage_DesignEditor_Block_Toolbar_HandlesHierarchy();
        $this->_block->setLayout($layoutUtility->getLayoutFromFixture($pageTypesFixture));
    }

    public function testRenderHierarchy()
    {
        $expected = __DIR__ . '/_files/_handles_hierarchy.html';
        $actual = $this->_block->renderHierarchy();
        $this->assertXmlStringEqualsXmlFile($expected, $actual);
    }

    public function testGetSelectedHandleFromPageHandles()
    {
        $this->_block->getLayout()->getUpdate()->addPageHandles(array('catalog_product_view_type_simple'));
        $this->assertEquals('catalog_product_view_type_simple', $this->_block->getSelectedHandle());
    }

    public function testGetSelectedHandleFromAnyHandles()
    {
        $this->_block->getLayout()->getUpdate()->addHandle(array(
            'catalog_product_view',
            'catalog_product_view_type_grouped',
            'not_a_page_type',
        ));
        $this->assertEquals('catalog_product_view_type_grouped', $this->_block->getSelectedHandle());
    }

    public function testGetSelectedHandleLabel()
    {
        $this->assertNull($this->_block->getSelectedHandleLabel());
        $this->_block->setSelectedHandle('default');
        $this->assertEquals('All Pages', $this->_block->getSelectedHandleLabel());
    }

    public function testSetSelectedHandle()
    {
        $this->assertFalse($this->_block->getSelectedHandle());
        $this->_block->setSelectedHandle('catalog_product_view_type_configurable');
        $this->assertEquals('catalog_product_view_type_configurable', $this->_block->getSelectedHandle());
    }
}
