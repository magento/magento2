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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Block_Product_Configurable_AssociatedSelector_Backend_Grid_ColumnSetTest
    extends PHPUnit_Framework_TestCase
{

    /**
     * Testing adding column with configurable attribute to column set
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Catalog/_files/product_configurable.php
     */
    public function testPrepareSelect()
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1); // fixture
        Mage::register('current_product', $product);

        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('Mage_Core_Model_Layout');
        /** @var $block  Mage_Catalog_Block_Product_Configurable_AssociatedSelector_Backend_Grid_ColumnSet */
        $block = $layout->createBlock(
            'Mage_Catalog_Block_Product_Configurable_AssociatedSelector_Backend_Grid_ColumnSet',
            'block'
        );
        $assertBlock = $block->getLayout()->getBlock('block.test_configurable');
        $this->assertEquals('Test Configurable', $assertBlock->getHeader());
        $this->assertEquals('test_configurable', $assertBlock->getId());
    }
}
