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

/**
 * @group module:Mage_DesignEditor
 */
class Mage_DesignEditor_Block_Toolbar_PageTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Block_Toolbar_PageType
     */
    protected $_block;

    protected function setUp()
    {
        $layoutUtility = new Mage_Core_Utility_Layout($this);
        $pageTypesFixture = __DIR__ . '/../../../Core/Model/Layout/_files/_page_types.xml';
        $layout = $layoutUtility->getLayoutFromFixture($pageTypesFixture);
        $layout->getUpdate()->addPageHandles(array('catalog_product_view_type_simple'));
        $this->_block = new Mage_DesignEditor_Block_Toolbar_PageType();
        $this->_block->setLayout($layout);
    }

    public function testRenderPageTypes()
    {
        $expected = __DIR__ . '/_files/_page_types_hierarchy.html';
        $actual = $this->_block->renderPageTypes();
        $this->assertXmlStringEqualsXmlFile($expected, $actual);
    }

    public function testGetSelectedPageType()
    {
        $this->assertEquals('catalog_product_view_type_simple', $this->_block->getSelectedPageType());
        $this->_block->setSelectedPageType('catalog_product_view_type_configurable');
        $this->assertEquals('catalog_product_view_type_configurable', $this->_block->getSelectedPageType());
    }
}
