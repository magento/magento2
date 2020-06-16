<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for order totals block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class TotalsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Layout */
    private $layout;

    /** @var Totals */
    private $block;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Totals::class, 'block');
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testToHtmlChildrenInitialized(): void
    {
        $this->block->setOrder($this->orderFactory->create())->setTemplate('order/totals.phtml');
        $context = $this->objectManager->get(Context::class);
        $childOne = $this->getMockBuilder(Text::class)
            ->setMethods(['initTotals'])
            ->setConstructorArgs([$context])
            ->getMock();
        $childOne->expects($this->once())->method('initTotals');
        $this->layout->addBlock($childOne, 'child1', 'block');
        $childTwo = $this->getMockBuilder(Text::class)
            ->setMethods(['initTotals'])
            ->setConstructorArgs([$context])
            ->getMock();
        $childTwo->expects($this->once())->method('initTotals');
        $this->layout->addBlock($childTwo, 'child2', 'block');
        $childThree = $this->getMockBuilder(Text::class)
            ->setMethods(['initTotals'])
            ->setConstructorArgs([$context])
            ->getMock();
        $childThree->expects($this->once())->method('initTotals');
        $this->layout->addBlock($childThree, 'child3', 'block');
        $this->block->toHtml();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_two_items.php
     *
     * @return void
     */
    public function testOrderTotalsBlock(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $blockHtml = $this->block->setTemplate('Magento_Sales::order/totals.phtml')->setOrder($order)->toHtml();
        $message = '"%s" for order wasn\'t found or not equals to %01.2f';
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Subtotal'),
                    $order->getSubtotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Subtotal'), $order->getSubtotal())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Shipping & Handling'),
                    $order->getShippingAmount()
                ),
                $blockHtml
            ),
            sprintf($message, __('Shipping & Handling'), $order->getShippingAmount())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//tr[contains(@class, 'grand_total') and contains(.//strong, '%s')]"
                    . "//span[contains(text(), '%01.2f')]",
                    __('Grand Total'),
                    $order->getGrandTotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Grand Total'), $order->getGrandTotal())
        );
    }
}
