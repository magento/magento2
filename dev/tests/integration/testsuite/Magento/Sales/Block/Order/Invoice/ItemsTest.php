<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\Invoice;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Sales\Block\Order\Invoice\Items
     */
    protected $_block;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_invoice;

    protected function setUp()
    {
        $this->_layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $this->_block = $this->_layout->createBlock('Magento\Sales\Block\Order\Invoice\Items', 'block');
        $this->_invoice = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Invoice'
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetInvoiceTotalsHtml()
    {
        $childBlock = $this->_layout->addBlock('Magento\Framework\View\Element\Text', 'invoice_totals', 'block');

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getInvoice());
        $this->assertNotEquals($expectedHtml, $this->_block->getInvoiceTotalsHtml($this->_invoice));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getInvoiceTotalsHtml($this->_invoice);
        $this->assertSame($this->_invoice, $childBlock->getInvoice());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    public function testGetInvoiceCommentsHtml()
    {
        $childBlock = $this->_layout->addBlock('Magento\Framework\View\Element\Text', 'invoice_comments', 'block');

        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getEntity());
        $this->assertEmpty($childBlock->getTitle());
        $this->assertNotEquals($expectedHtml, $this->_block->getInvoiceCommentsHtml($this->_invoice));

        $childBlock->setText($expectedHtml);
        $actualHtml = $this->_block->getInvoiceCommentsHtml($this->_invoice);
        $this->assertSame($this->_invoice, $childBlock->getEntity());
        $this->assertNotEmpty($childBlock->getTitle());
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
