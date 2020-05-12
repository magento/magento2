<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Item\Renderer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for default renderer order items.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class DefaultRendererTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var DefaultRenderer */
    private $block;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(DefaultRenderer::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/shipment_for_order_with_customer.php
     *
     * @return void
     */
    public function testDisplayingShipmentItem(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        $this->assertNotNull($shipment->getId());
        $item = $shipment->getAllItems()[0] ?? null;
        $this->assertNotNull($item);
        $blockHtml = $this->block->setTemplate('Magento_Sales::order/shipment/items/renderer/default.phtml')
            ->setItem($item)->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
                    $item->getName()
                ),
                $blockHtml
            ),
            sprintf('Item with name %s wasn\'t found.', $item->getName())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//td[contains(@class, 'sku') and contains(text(), '%s')]",
                    $item->getSku()
                ),
                $blockHtml
            ),
            sprintf('Item with sku %s wasn\'t found.', $item->getSku())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//td[contains(@class, 'qty') and contains(text(), '%d')]",
                    $item->getQty()
                ),
                $blockHtml
            ),
            sprintf(
                'Qty for item %s wasn\'t found or not equals to %s.',
                $item->getName(),
                $item->getQty()
            )
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/refunds_for_items.php
     *
     * @return void
     */
    public function testCreditmemoItemTotalAmount(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $creditmemo = $order->getCreditmemosCollection()->getFirstItem();
        $this->assertNotNull($creditmemo->getId());
        $item = $creditmemo->getItemsCollection()->getFirstItem();
        $this->assertNotNull($item->getId());
        $this->assertEquals(10.00, $this->block->getTotalAmount($item));
    }
}
