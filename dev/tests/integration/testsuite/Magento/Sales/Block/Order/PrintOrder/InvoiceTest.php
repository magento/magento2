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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Order\PrintOrder;

class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetInvoiceTotalsHtml()
    {
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->register('current_order', $order);
        $payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Payment'
        );
        $payment->setMethod('checkmo');
        $order->setPayment($payment);

        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $block = $layout->createBlock('Magento\Sales\Block\Order\PrintOrder\Invoice', 'block');
        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Text', 'invoice_totals', 'block');

        $expectedHtml = '<b>Any html</b>';
        $invoice = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Sales\Model\Order\Invoice'
        );
        $this->assertEmpty($childBlock->getInvoice());
        $this->assertNotEquals($expectedHtml, $block->getInvoiceTotalsHtml($invoice));

        $childBlock->setText($expectedHtml);
        $actualHtml = $block->getInvoiceTotalsHtml($invoice);
        $this->assertSame($invoice, $childBlock->getInvoice());
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
