<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart\Index;

/**
 * @magentoDbIsolation enabled
 */
class CouponPostTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test for \Magento\Checkout\Controller\Cart\CouponPost::execute() with invalid coupon
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function testExecuteInvalidCoupon()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
        $quote = $session->getQuote();
        $quote->setData('trigger_recollect', 1)->setTotalsCollectedFlag(true);
        $inputData = [
            'remove' => 0,
            'coupon_code' => 'test'
        ];
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
     * Test for \Magento\Checkout\Controller\Cart\CouponPost::execute() with valid coupon
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_coupon_saved.php
     */
    public function testExecuteValidCoupon()
    {
        /** @var $session \Magento\Checkout\Model\Session */
        $session = $this->_objectManager->create(\Magento\Checkout\Model\Session::class);
        $quote = $session->getQuote();
        $quote->setData('trigger_recollect', 1)->setTotalsCollectedFlag(true);

        $salesRuleFactory = $this->_objectManager->get(\Magento\SalesRule\Model\RuleFactory::class);
        $salesRule = $salesRuleFactory->create();
        $salesRuleId = $this->_objectManager->get(\Magento\Framework\Registry::class)
            ->registry('Magento/Checkout/_file/discount_10percent');
        $salesRule->load($salesRuleId);
        $couponCode = $salesRule->getPrimaryCoupon()->getCode();

        $inputData = [
            'remove' => 0,
            'coupon_code' => $couponCode
        ];
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
