<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for order billing method form.
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    /**
     * Checks if billing method form generates contentUpdated event
     * to parse elements with data-mage-init attributes.
     */
    public function testContentUpdated()
    {
        /** @var Form $block */
        $block = $this->layout->createBlock(Form::class, 'order_billing_method');
        $block->setTemplate('Magento_Sales::order/create/billing/method/form.phtml');

        $html = $block->toHtml();
        $this->assertContains('mage.apply()', $html);
        $this->assertContains("order.setPaymentMethod('" . $block->escapeHtml($block->getSelectedMethodCode()) . "')", $html);
    }
}
