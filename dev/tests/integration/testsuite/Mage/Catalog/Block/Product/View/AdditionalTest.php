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

class Mage_Catalog_Block_Product_View_AdditionalTest extends PHPUnit_Framework_TestCase
{
    public function testGetChildHtmlList()
    {
        $layout = new Mage_Core_Model_Layout;
        $block = new Mage_Catalog_Block_Product_View_Additional;
        $layout->addBlock($block, 'block');

        $child1 = $layout->addBlock('Mage_Core_Block_Text', 'child1', 'block');
        $expectedHtml1 = '<b>Any html of child1</b>';
        $child1->setText($expectedHtml1);

        $child2 = $layout->addBlock('Mage_Core_Block_Text', 'child2', 'block');
        $expectedHtml2 = '<b>Any html of child2</b>';
        $child2->setText($expectedHtml2);

        $list = $block->getChildHtmlList();

        $this->assertInternalType('array', $list);
        $this->assertCount(2, $list);
        $this->assertContains($expectedHtml1, $list);
        $this->assertContains($expectedHtml2, $list);
    }
}
