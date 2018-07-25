<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Model\Quote;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\BillingAddressManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\Checkout\Plugin\Model\Quote\ResetQuoteAddresses
 */
class ResetQuoteAddressesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     *
     * @return void
     */
    public function testAfterRemoveItem(): void
    {
        /** @var Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        $quote->load('test_order_with_virtual_product', 'reserved_order_id');
        /** @var QuoteAddress $quoteShippingAddress */
        $quoteBillingAddress = Bootstrap::getObjectManager()->create(QuoteAddress::class);
        $quoteBillingAddress->setRegion('CA')
            ->setPostcode('90210')
            ->setFirstname('a_unique_firstname')
            ->setLastname('lastname')
            ->setStreet('street')
            ->setCity('Beverly Hills')
            ->setEmail('admin@example.com')
            ->setTelephone('1111111111')
            ->setCountryId('US')
            ->setAddressType('billing');

        /** @var BillingAddressManagement $billingAddressManagement */
        $billingAddressManagement = Bootstrap::getObjectManager()->create(BillingAddressManagement::class);
        $billingAddressManagement->assign($quote->getId(), $quoteBillingAddress);
        /** @var Session $checkoutSession */
        $checkoutSession = Bootstrap::getObjectManager()->create(Session::class);
        $checkoutSession->setQuoteId($quote->getId());
        /** @var Cart $cart */
        $cart = Bootstrap::getObjectManager()->create(Cart::class);

        $activeQuote = $cart->getQuote();
        $cart->removeItem($activeQuote->getAllVisibleItems()[0]->getId());
        $cart->save();

        /** @var Quote $quote */
        $quote = Bootstrap::getObjectManager()->create(Quote::class);
        $quote->load('test_order_with_virtual_product', 'reserved_order_id');
        $quoteBillingAddressUpdated = $quote->getBillingAddress();
        $customer = $quote->getCustomer();

        $this->assertEquals($quoteBillingAddressUpdated->getEmail(), $customer->getEmail());
        $this->assertEmpty($quoteBillingAddressUpdated->getCountryId());
        $this->assertEmpty($quoteBillingAddressUpdated->getRegionId());
        $this->assertEmpty($quoteBillingAddressUpdated->getRegion());
        $this->assertEmpty($quoteBillingAddressUpdated->getPostcode());
        $this->assertEmpty($quoteBillingAddressUpdated->getCity());
    }
}
