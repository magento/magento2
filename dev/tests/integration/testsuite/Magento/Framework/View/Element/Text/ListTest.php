<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Text;

class ListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\View\Element\Text\ListText
     */
    protected $_block;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_block = $this->_layout->createBlock('Magento\Framework\View\Element\Text\ListText');
    }

    public function testToHtml()
    {
        $children = [
            ['block1', 'Magento\Framework\View\Element\Text', 'text1'],
            ['block2', 'Magento\Framework\View\Element\Text', 'text2'],
            ['block3', 'Magento\Framework\View\Element\Text', 'text3'],
        ];
        foreach ($children as $child) {
            $this->_layout->addBlock($child[1], $child[0], $this->_block->getNameInLayout())->setText($child[2]);
        }
        $html = $this->_block->toHtml();
        $this->assertEquals('text1text2text3', $html);
    }

    public function testToHtmlWithContainer()
    {
        $listName = $this->_block->getNameInLayout();
        $block1 = $this->_layout->addBlock('Magento\Framework\View\Element\Text', '', $listName);
        $this->_layout->addContainer('container', 'Container', [], $listName);
        $block2 = $this->_layout->addBlock('Magento\Framework\View\Element\Text', '', 'container');
        $block3 = $this->_layout->addBlock('Magento\Framework\View\Element\Text', '', $listName);
        $block1->setText('text1');
        $block2->setText('text2');
        $block3->setText('text3');
        $html = $this->_block->toHtml();
        $this->assertEquals('text1text2text3', $html);
    }
}
