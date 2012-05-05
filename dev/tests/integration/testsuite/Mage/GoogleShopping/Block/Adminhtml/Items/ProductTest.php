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
 * @package     Mage_GoogleShopping
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_GoogleShopping_Block_Adminhtml_Items_ProductTest extends PHPUnit_Framework_TestCase
{
    public function testBeforeToHtml()
    {
        $this->markTestIncomplete('Mage_GoogleShopping is not implemented yet');

        $block = new Mage_GoogleShopping_Block_Adminhtml_Items_Product;
        $filter = new Mage_Core_Block_Text;
        $search = new Mage_Core_Block_Text;

        $layout = new Mage_Core_Model_Layout;
        $layout->addBlock($block, 'product');
        $layout->addBlock($filter, 'reset_filter_button', 'product');
        $layout->addBlock($search, 'search_button', 'product');
        $block->toHtml();

        $this->assertEquals('googleshopping_selection_search_grid_JsObject.resetFilter()', $filter->getData('onclick'));
        $this->assertEquals('googleshopping_selection_search_grid_JsObject.doFilter()', $search->getData('onclick'));
    }
}
