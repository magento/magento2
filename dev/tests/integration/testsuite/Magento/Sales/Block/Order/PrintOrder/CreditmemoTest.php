<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\PrintOrder;

class CreditmemoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetTotalsHtml()
    {
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_order', $order);
        $payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Payment::class
        );
        $payment->setMethod('checkmo');
        $order->setPayment($payment);

        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        $block = $layout->createBlock(\Magento\Sales\Block\Order\PrintOrder\Creditmemo::class, 'block');
        $childBlock = $layout->addBlock(\Magento\Framework\View\Element\Text::class, 'creditmemo_totals', 'block');

        $expectedHtml = '<b>Any html</b>';
        $creditmemo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Creditmemo::class
        );
        $this->assertEmpty($childBlock->getCreditmemo());
        $this->assertNotEquals($expectedHtml, $block->getTotalsHtml($creditmemo));

        $childBlock->setText($expectedHtml);
        $actualHtml = $block->getTotalsHtml($creditmemo);
        $this->assertSame($creditmemo, $childBlock->getCreditmemo());
        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
