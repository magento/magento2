<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart\Index;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoDbIsolation enabled
 */
class CouponPostTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test for \Magento\Checkout\Controller\Cart\CouponPost::execute() with simple product
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testExecute()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
        $quote = $session->getQuote();
        $quote->setData('trigger_recollect', 1)->setTotalsCollectedFlag(true);
        $inputData = [
            'remove' => 0,
            'coupon_code' => 'test'
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($inputData);
        $this->dispatch(
            'checkout/cart/couponPost/'
        );

        $this->assertSessionMessages(
            $this->equalTo(['The coupon code &quot;test&quot; is not valid.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Testing by adding a valid coupon to cart
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     * @magentoDataFixture Magento/Usps/Fixtures/cart_rule_coupon_free_shipping.php
     * @return void
     */
    public function testAddingValidCoupon(): void
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
        $quote = $session->getQuote();
        $quote->setData('trigger_recollect', 1)->setTotalsCollectedFlag(true);

        $couponCode = 'IMPHBR852R61';
        $inputData = [
            'remove' => 0,
            'coupon_code' => $couponCode
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($inputData);
        $this->dispatch(
            'checkout/cart/couponPost/'
        );

        $this->assertSessionMessages(
            $this->equalTo(['You used coupon code &quot;' . $couponCode . '&quot;.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
