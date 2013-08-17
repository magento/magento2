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
 * @category    Mage
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_TabsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testPrepareLayout()
    {
        Mage::getDesign()->setArea(Mage_Core_Model_App_Area::AREA_ADMINHTML)->setDefaultDesignTheme();
        Mage::getConfig()->setCurrentAreaCode(Mage::helper("Mage_Backend_Helper_Data")->getAreaCode());
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1); // fixture
        Mage::register('product', $product);

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        $layout->addBlock('Mage_Core_Block_Text', 'head');
        /** @var $block Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs */
        $block = $layout->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs');
        $this->assertArrayHasKey(0, $block->getTabsIds());
        $this->assertNotEmpty($layout->getBlock('catalog_product_edit_tabs'));
    }
}
