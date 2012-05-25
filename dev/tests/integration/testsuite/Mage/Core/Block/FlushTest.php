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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Block_FlushTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_Core_Block_Text_List
     */
    protected $_block;

    protected function setUp()
    {
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_block = $this->_layout->createBlock('Mage_Core_Block_Flush');
    }

    public function testToHtml()
    {
        $children = array(
            array('block1', 'Mage_Core_Block_Text', 'text1'),
            array('block2', 'Mage_Core_Block_Text', 'text2'),
            array('block3', 'Mage_Core_Block_Text', 'text3'),
        );
        foreach ($children as $child) {
            $this->_layout->addBlock($child[1], $child[0], $this->_block->getNameInLayout())->setText($child[2]);
        }
        ob_start();
        $this->_block->toHtml();
        $html = ob_get_clean();
        $this->assertEquals('text1text2text3', $html);
    }

    public function testToHtmlWithContainer()
    {
        $listName = $this->_block->getNameInLayout();
        $block1 = $this->_layout->addBlock('Mage_Core_Block_Text', '', $listName);
        $this->_layout->addContainer('container', 'Container', array(), $listName);
        $block2 = $this->_layout->addBlock('Mage_Core_Block_Text', '', 'container');
        $block3 = $this->_layout->addBlock('Mage_Core_Block_Text', '', $listName);
        $block1->setText('text1');
        $block2->setText('text2');
        $block3->setText('text3');
        ob_start();
        $this->_block->toHtml();
        $html = ob_get_clean();
        $this->assertEquals('text1text2text3', $html);
    }
}
