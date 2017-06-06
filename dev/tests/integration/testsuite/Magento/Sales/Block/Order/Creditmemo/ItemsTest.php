<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Creditmemo;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Sales\Block\Order\Creditmemo\Items
     */
    protected $_block;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $_creditmemo;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $this->_block = $this->_layout->createBlock(\Magento\Sales\Block\Order\Creditmemo\Items::class, 'block');
        $this->_creditmemo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Creditmemo::class
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetTotalsHtml()
    {
        $childBlock = $this->_layout->addBlock(
            \Magento\Framework\View\Element\Text::class,
            'creditmemo_totals',
            'block'
        );

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getCreditmemo());
        $this->assertNotEquals($expectedHtml, $this->_block->getTotalsHtml($this->_creditmemo));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getTotalsHtml($this->_creditmemo);
        $this->assertSame($this->_creditmemo, $childBlock->getCreditmemo());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    public function testGetCommentsHtml()
    {
        $childBlock = $this->_layout->addBlock(
            \Magento\Framework\View\Element\Text::class,
            'creditmemo_comments',
            'block'
        );

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getEntity());
        $this->assertEmpty($childBlock->getTitle());
        $this->assertNotEquals($expectedHtml, $this->_block->getCommentsHtml($this->_creditmemo));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getCommentsHtml($this->_creditmemo);
        $this->assertSame($this->_creditmemo, $childBlock->getEntity());
        $this->assertNotEmpty($childBlock->getTitle());
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
