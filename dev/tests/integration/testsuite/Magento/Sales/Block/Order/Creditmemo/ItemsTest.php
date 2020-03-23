<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Creditmemo;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for view creditmemo items block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Items */
    private $block;

    /** @var CreditmemoInterface */
    private $creditmemo;

    /** @var Registry */
    private $registry;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var PageFactory */
    private $pageFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $this->layout->createBlock(Items::class, 'block');
        $this->creditmemo = $this->objectManager->get(CreditmemoInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->registry->unregister('current_order');

        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetTotalsHtml(): void
    {
        $childBlock = $this->layout->addBlock(
            Text::class,
            'creditmemo_totals',
            'block'
        );
        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getCreditmemo());
        $this->assertNotEquals($expectedHtml, $this->block->getTotalsHtml($this->creditmemo));
        $childBlock->setText($expectedHtml);
        $actualHtml = $this->block->getTotalsHtml($this->creditmemo);
        $this->assertSame($this->creditmemo, $childBlock->getCreditmemo());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testGetCommentsHtml(): void
    {
        $childBlock = $this->layout->addBlock(
            Text::class,
            'creditmemo_comments',
            'block'
        );
        $expectedHtml = '<b>Any html</b>';
        $this->assertEmpty($childBlock->getEntity());
        $this->assertEmpty($childBlock->getTitle());
        $this->assertNotEquals($expectedHtml, $this->block->getCommentsHtml($this->creditmemo));
        $childBlock->setText($expectedHtml);
        $actualHtml = $this->block->getCommentsHtml($this->creditmemo);
        $this->assertSame($this->creditmemo, $childBlock->getEntity());
        $this->assertNotEmpty($childBlock->getTitle());
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/two_creditmemo_for_items.php
     *
     * @return void
     */
    public function testDisplayingCreditmemos(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $this->registerOrder($order);
        $blockHtml = $this->renderCreditmemoItemsBlock();
        $this->assertCreditmemosBlock($order, $blockHtml);
    }

    /**
     * Assert creditmemos block.
     *
     * @param OrderInterface $order
     * @param string $blockHtml
     * @return void
     */
    private function assertCreditmemosBlock(OrderInterface $order, string $blockHtml): void
    {
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@href, 'sales/order/printCreditmemo/order_id/%s')]/span[contains(text(), '%s')]",
                    $order->getId(),
                    __('Print All Refunds')
                ),
                $blockHtml
            ),
            sprintf('%s button was not found.', __('Print All Refunds'))
        );
        $this->assertNotCount(0, $order->getCreditmemosCollection(), 'Creditmemos collection is empty');
        foreach ($order->getCreditmemosCollection() as $creditmemo) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        "//div[contains(@class, 'order-title')]/strong[contains(text(), '%s')]",
                        __('Refund #') . $creditmemo->getIncrementId()
                    ),
                    $blockHtml
                ),
                sprintf('Title for %s was not found.', __('Refund #') . $creditmemo->getIncrementId())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        "//a[contains(@href, 'sales/order/printCreditmemo/creditmemo_id/%s')]"
                        . "/span[contains(text(), '%s')]",
                        $creditmemo->getId(),
                        __('Print Refund')
                    ),
                    $blockHtml
                ),
                sprintf('%s button for #%s was not found.', __('Print Refund'), $creditmemo->getIncrementId())
            );
            $this->assertCreditmemoItems($creditmemo, $blockHtml);
        }
    }

    /**
     * Assert creditmemo items list.
     *
     * @param CreditmemoInterface $creditmemo
     * @param string $html
     * @return void
     */
    private function assertCreditmemoItems(CreditmemoInterface $creditmemo, string $html): void
    {
        $this->assertNotCount(0, $creditmemo->getItemsCollection(), 'Creditmemo items collection is empty');
        foreach ($creditmemo->getItemsCollection() as $item) {
            $rowXpath = sprintf(
                "//table[@id='my-refund-table-%s']//tr[@id='order-item-row-%s']",
                $creditmemo->getId(),
                $item->getId()
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/td[contains(@class, 'name')]/strong[contains(text(), '%s')]",
                        $item->getName()
                    ),
                    $html
                ),
                sprintf('Item with name %s wasn\'t found.', $item->getName())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf($rowXpath . "/td[contains(@class, 'sku') and contains(text(), '%s')]", $item->getSku()),
                    $html
                ),
                sprintf('Item with sku %s wasn\'t found.', $item->getSku())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/td[contains(@class, 'price')]//span[contains(text(), '%01.2f')]",
                        $item->getPrice()
                    ),
                    $html
                ),
                sprintf('Price for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getPrice())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf($rowXpath . "/td[contains(@class, 'qty') and contains(text(), '%d')]", $item->getQty()),
                    $html
                ),
                sprintf('Qty for item %s wasn\'t found or not equals to %s.', $item->getName(), $item->getQty())
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/td[contains(@class, 'subtotal')]//span[contains(text(), '%01.2f')]",
                        $item->getRowTotal()
                    ),
                    $html
                ),
                sprintf(
                    'Subtotal for item %s wasn\'t found or not equals to %s.',
                    $item->getName(),
                    $item->getRowTotal()
                )
            );
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        $rowXpath . "/td[contains(@class, 'discount')]/span[contains(text(), '%01.2f')]",
                        $item->getDiscountAmount()
                    ),
                    $html
                ),
                sprintf(
                    'Discount for item %s wasn\'t found or not equals to %s.',
                    $item->getName(),
                    $item->getDiscountAmount()
                )
            );
        }
    }

    /**
     * Render creditmemo items block.
     *
     * @return string
     */
    private function renderCreditmemoItemsBlock(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'sales_order_creditmemo',
        ]);
        $page->getLayout()->generateXml();
        $creditmemoItemsBlock = $page->getLayout()->getBlock('creditmemo_items')->unsetChild('creditmemo_totals');
        $creditmemoItemsBlock->getRequest()->setRouteName('sales')->setControllerName('order')
            ->setActionName('creditmemo');

        return $creditmemoItemsBlock->toHtml();
    }

    /**
     * Register order in registry.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function registerOrder(OrderInterface $order): void
    {
        $this->registry->unregister('current_order');
        $this->registry->register('current_order', $order);
    }
}
