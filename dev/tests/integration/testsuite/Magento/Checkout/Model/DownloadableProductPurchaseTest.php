<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory as DownloadableLinkItemCollection;
use Magento\Downloadable\Test\Fixture\DownloadableProduct as DownloadableProductFixture;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for purchasing downloadable products through checkout
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadableProductPurchaseTest extends TestCase
{
    /**
     * @var TransportBuilderMock
     */
    private TransportBuilderMock $transportBuilder;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var DownloadableLinkItemCollection
     */
    private DownloadableLinkItemCollection $linkCollection;

    /**
     * @var InvoiceOrderInterface
     */
    private InvoiceOrderInterface $invoiceOrder;

    /**
     * @var GuestCartManagementInterface
     */
    private GuestCartManagementInterface $cartManagement;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->transportBuilder = $this->objectManager->get(TransportBuilderMock::class);
        $this->linkCollection = $this->objectManager
            ->get(DownloadableLinkItemCollection::class);
        $this->invoiceOrder = $this->objectManager->get(InvoiceOrderInterface::class);
        $this->cartManagement = $this->objectManager->get(GuestCartManagementInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->transportBuilder->clean();
    }

    /**
     * Clean up after test
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->transportBuilder->clean();
    }

    /**
     * Test that after placing an order for a downloadable product, the order email contains the correct links
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(DownloadableProductFixture::class, [
            'price' => 100,
            'type_id' => 'downloadable',
            'links_purchased_separately' => 0,
            'downloadable_product_links' => [
                [
                    'title' => 'Example 1',
                    'price' => 0.00,
                    'link_type' => 'url'
                ],
                [
                    'title' => 'Example 2',
                    'price' => 0.00,
                    'link_type' => 'url'
                ]
            ]
        ], as: 'product'),
        DataFixture(GuestCart::class, [], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$'], as: 'billingAddress'),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
    ]
    public function testDownloadableProductLinkAfterOrderPlaced(): void
    {
        $quoteId = (int) $this->fixtures->get('quote')->getEntityId();
        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $checkoutSession->setQuoteId($quoteId);
        $order = $this->placeOrderWithDownloadableProduct();
        $this->verifyOrderConfirmationEmailSent($order);
        $this->verifyDownloadableLinksStatus($order);
    }

    /**
     * Verifies that order confirmation email is sent and contains expected content
     *
     * @param OrderInterface $order The order to verify email for
     * @throws NoSuchEntityException
     */
    private function verifyOrderConfirmationEmailSent(OrderInterface $order): void
    {
        $message = $this->transportBuilder->getSentMessage();
        $this->assertNotNull($message, 'Order confirmation email should be sent for downloadable product order');
        $assert = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $order->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Order <span class=\"no-link\">#{$order->getIncrementId()}</span>"
            )
        );
        $emailBody = quoted_printable_decode($message->getBody()->bodyToString());
        $this->assertThat($emailBody, $assert);
        $this->assertStringContainsString('download', $emailBody);
        $this->assertStringContainsString('Downloadable Links', $emailBody);
        $this->assertStringContainsString('/downloadable/download/link/', $emailBody);
    }

    /**
     * Verifies downloadable link status for guest order items
     *
     * @param OrderInterface $order
     */
    private function verifyDownloadableLinksStatus(OrderInterface $order): void
    {
        foreach ($order->getItems() as $item) {
            if ($item->getData('item_id')) {
                $this->checkDownloadableLinkStatusForGuestOrder(
                    (int)$item->getData('item_id'),
                    (int)$order->getData('entity_id')
                );
            }
        }
    }

    /**
     * Check downloadable link status before and after invoice for guest order
     *
     * @param int $orderItemId
     * @param int $orderId
     * @return void
     */
    private function checkDownloadableLinkStatusForGuestOrder(int $orderItemId, int $orderId): void
    {
        $purchasedLinks = $this->getDownloadablePurchasedLinks($orderItemId);
        foreach ($purchasedLinks as $link) {
            $this->assertEquals(
                Item::LINK_STATUS_PENDING,
                $link->getStatus(),
                'Download link should be pending before invoice for guest order'
            );
        }
        $this->createInvoiceForOrder($orderId);
        $linksAfterInvoice = $this->getDownloadablePurchasedLinks($orderItemId);
        foreach ($linksAfterInvoice as $link) {
            $this->assertEquals(
                Item::LINK_STATUS_AVAILABLE,
                $link->getStatus(),
                'Download link should be available after invoice for guest order'
            );
        }
    }

    /**
     * Retrieve purchased downloadable links by order item id
     *
     * @param int $orderItemId
     * @return array
     */
    private function getDownloadablePurchasedLinks(int $orderItemId): array
    {
        /** @var DownloadableLinkItemCollection $collection */
        $collection = $this->linkCollection->create()
            ->addFieldToFilter('order_item_id', $orderItemId);

        return $collection->getItems();
    }

    /**
     * Create invoice for order
     *
     * @param int $orderId
     * @return void
     */
    private function createInvoiceForOrder(int $orderId): void
    {
        $this->invoiceOrder->execute(
            $orderId
        );
    }

    /**
     * Place order with downloadable product in quote
     *
     * @return OrderInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function placeOrderWithDownloadableProduct(): OrderInterface
    {
        $quote = $this->fixtures->get('quote');
        $maskedId = $this->quoteIdToMaskedId->execute(
            (int) $quote->getEntityId()
        );
        $orderId = $this->cartManagement->placeOrder($maskedId);

        return $this->orderRepository->get($orderId);
    }
}
