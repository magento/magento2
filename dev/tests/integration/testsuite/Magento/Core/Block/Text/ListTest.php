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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Block\Text;

class ListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Core\Block\Text\ListText
     */
    protected $_block;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\View\LayoutInterface');
        $this->_block = $this->_layout->createBlock('Magento\Core\Block\Text\ListText');
    }

    public function testToHtml()
    {
        $children = array(
            array('block1', 'Magento\Core\Block\Text', 'text1'),
            array('block2', 'Magento\Core\Block\Text', 'text2'),
            array('block3', 'Magento\Core\Block\Text', 'text3'),
        );
        foreach ($children as $child) {
            $this->_layout->addBlock($child[1], $child[0], $this->_block->getNameInLayout())
                ->setText($child[2]);
        }
        $html = $this->_block->toHtml();
        $this->assertEquals('text1text2text3', $html);
    }

    public function testToHtmlWithContainer()
    {
        $listName = $this->_block->getNameInLayout();
        $block1 = $this->_layout->addBlock('Magento\Core\Block\Text', '', $listName);
        $this->_layout->addContainer('container', 'Container', array(), $listName);
        $block2 = $this->_layout->addBlock('Magento\Core\Block\Text', '', 'container');
        $block3 = $this->_layout->addBlock('Magento\Core\Block\Text', '', $listName);
        $block1->setText('text1');
        $block2->setText('text2');
        $block3->setText('text3');
        $html = $this->_block->toHtml();
        $this->assertEquals('text1text2text3', $html);
    }
}
