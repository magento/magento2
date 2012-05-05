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

class Mage_Sales_Block_Order_Invoice_ItemsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_Sales_Block_Order_Invoice_Items
     */
    protected $_block;

    /**
     * @var Mage_Sales_Model_Order_Invoice
     */
    protected $_invoice;

    public function setUp()
    {
        $this->_layout = new Mage_Core_Model_Layout;
        $this->_block = new Mage_Sales_Block_Order_Invoice_Items;
        $this->_layout->addBlock($this->_block, 'block');
        $this->_invoice = new Mage_Sales_Model_Order_Invoice;
    }

    public function testGetInvoiceTotalsHtml()
    {
        $childBlock = $this->_layout->addBlock('Mage_Core_Block_Text', 'invoice_totals', 'block');

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
        $childBlock = $this->_layout->addBlock('Mage_Core_Block_Text', 'invoice_comments', 'block');

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
