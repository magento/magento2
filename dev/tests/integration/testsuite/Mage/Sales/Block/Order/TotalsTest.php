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
 * @package     Mage_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Sales
 */
class Mage_Sales_Block_Order_TotalsTest extends PHPUnit_Framework_TestCase
{
    public function testToHtmlChildrenInitialized()
    {
        $block = new Mage_Sales_Block_Order_Totals;
        $block->setOrder(new Mage_Sales_Model_Order)
            ->setTemplate('order/totals.phtml');
        $layout = new Mage_Core_Model_Layout;
        $layout->addBlock($block, 'block');

        $child1 = $this->getMock('Mage_Core_Block_Text', array('initTotals'));
        $child1->expects($this->once())
            ->method('initTotals');
        $layout->addBlock($child1, 'child1', 'block');

        $layout->addBlock('Mage_Core_Block_Text', 'child2', 'block');

        $child3 = $this->getMock('Mage_Core_Block_Text', array('initTotals'));
        $child3->expects($this->once())
            ->method('initTotals');
        $layout->addBlock($child3, 'child3', 'block');

        $block->toHtml();
    }
}
