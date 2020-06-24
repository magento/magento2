<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Creditmemo;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for view creditmemo totals block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class TotalsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

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
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Totals::class)
            ->setTemplate('Magento_Sales::order/totals.phtml');
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/refunds_for_items.php
     *
     * @return void
     */
    public function testCreditmemoTotalsBlock(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $creditmemo = $order->getCreditmemosCollection()->getFirstItem();
        $this->assertNotNull($creditmemo->getId());
        $blockHtml = $this->block->setOrder($order)->setCreditmemo($creditmemo)->toHtml();
        $message = '"%s" for creditmemo wasn\'t found or not equals to %01.2f';
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Subtotal'),
                    $creditmemo->getSubtotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Subtotal'), $creditmemo->getSubtotal())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Shipping & Handling'),
                    $creditmemo->getShippingAmount()
                ),
                $blockHtml
            ),
            sprintf($message, __('Shipping & Handling'), $creditmemo->getShippingAmount())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//tr[contains(@class, 'grand_total') and contains(.//strong, '%s')]"
                    . "//span[contains(text(), '%01.2f')]",
                    __('Grand Total'),
                    $creditmemo->getGrandTotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Grand Total'), $creditmemo->getGrandTotal())
        );
    }
}
