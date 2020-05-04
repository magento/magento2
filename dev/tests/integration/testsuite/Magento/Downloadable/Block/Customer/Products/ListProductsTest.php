<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Block\Customer\Products;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class to check My Downloadable products tab content
 *
 * @see ListProducts
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProductsTest extends TestCase
{
    /** @var string */
    private $downloadLinkXpath = "//a[contains(@href, 'downloadable/download/link') and contains(text(), '%s')]";

    /** @var string */
    private $statusXpath = "//table[@id='my-downloadable-products-table']"
    . "//td[@data-th='Status' and contains(text(), '%s')]";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var Session */
    private $customerSession;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var InvoiceOrderInterface */
    private $invoiceOrder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->invoiceOrder = $this->objectManager->get(InvoiceOrderInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->logout();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testNoItems(): void
    {
        $html = $this->createBlock()->toHtml();
        $this->assertStringContainsString(
            (string)__('You have not purchased any downloadable products yet.'),
            strip_tags($html)
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product.php
     *
     * @return void
     */
    public function testPendingOrder(): void
    {
        $this->customerSession->loginById(1);
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                sprintf($this->downloadLinkXpath, 'Downloadable Product Link'),
                $this->createBlock()->toHtml()
            ),
            'The download link displayed'
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product.php
     *
     * @return void
     */
    public function testCompleteOrder(): void
    {
        $order = $this->getOrder('100000001');
        $this->invoiceOrder->execute($order->getId());
        $this->customerSession->loginById(1);
        $html = $this->createBlock()->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->downloadLinkXpath, 'Downloadable Product Link'), $html),
            'The download link is not found'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->statusXpath, (string)__('Available')), $html),
            'Wrong status displayed'
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/order_with_customer_and_downloadable_product.php
     *
     * @return void
     */
    public function testClosedOrder(): void
    {
        $order = $this->getOrder('100000001');
        $this->invoiceOrder->execute($order->getId());
        $this->objectManager->removeSharedInstance(OrderRepository::class);
        $refundOrder = $this->objectManager->create(RefundOrderInterface::class);
        $refundOrder->execute($order->getId());
        $this->customerSession->loginById(1);
        $html = $this->createBlock()->toHtml();
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(sprintf($this->downloadLinkXpath, 'Downloadable Product Link'), $html),
            'The download link is displayed for closed order'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(sprintf($this->statusXpath, (string)__('Expired')), $html),
            'Wrong status displayed'
        );
    }

    /**
     * Load order by increment id
     *
     * @param $orderIncrementId
     * @return OrderInterface
     */
    private function getOrder($orderIncrementId): OrderInterface
    {
        $order = $this->objectManager->get(OrderFactory::class)->create();

        return $order->loadByIncrementId($orderIncrementId);
    }

    /**
     * Create ProductsList block
     *
     * @return ListProducts
     */
    private function createBlock(): ListProducts
    {
        $block = $this->objectManager->create(ListProducts::class);
        $block->setTemplate('Magento_Downloadable::customer/products/list.phtml');
        $this->layout->addBlock($block, 'downloadable_customer_products_list');

        return $block;
    }
}
