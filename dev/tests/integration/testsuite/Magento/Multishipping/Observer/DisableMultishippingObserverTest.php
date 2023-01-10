<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * 'Disable Multishipping' observer integration tests.
 *
 * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
 * @magentoAppArea frontend
 */
class DisableMultishippingObserverTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cart = $this->objectManager->get(Cart::class);
        $this->prepareQuote();
    }

    /**
     * Test that Quote totals are calculated correctly when Cart is saved with 'Multishipping' mode enabled.
     *
     * @return void
     */
    public function testObserverWithEnabledMultishippingMode(): void
    {
        $quote = $this->cart->getQuote();
        $extensionAttributes = $quote->getExtensionAttributes();
        $this->assertEquals(1, (int)$quote->getItemsQty());
        $this->assertCount(1, $extensionAttributes->getShippingAssignments());

        $quote->setIsMultiShipping(1);
        $quoteItem = current($quote->getItems());
        $itemData = [$quoteItem->getId() => ['qty' => 2]];

        $this->cart->updateItems($itemData)->save();

        $this->assertEquals(2, (int)$quote->getItemsQty());
        $this->assertEquals(0, $quote->getIsMultiShipping());
        $this->assertCount(0, $extensionAttributes->getShippingAssignments());
    }

    /**
     * Prepare Quote before test execution.
     *
     * @return void
     */
    private function prepareQuote(): void
    {
        /** @var CartInterface $quote */
        $quote = $this->objectManager->get(GetQuoteByReservedOrderId::class)
            ->execute('test_order_with_simple_product_without_address');
        $shippingAssignment = $this->objectManager->get(ShippingAssignmentInterface::class);
        $quote->getExtensionAttributes()->setShippingAssignments([$shippingAssignment]);
        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = $this->objectManager->get(CheckoutSession::class);
        $checkoutSession->clearQuote();
        $checkoutSession->setQuoteId($quote->getId());
    }
}
