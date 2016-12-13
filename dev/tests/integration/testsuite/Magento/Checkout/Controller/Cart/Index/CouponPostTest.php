<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart\Index;

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
        $this->getRequest()->setPostValue($inputData);
        $this->dispatch(
            'checkout/cart/couponPost/'
        );

        $this->assertSessionMessages(
            $this->equalTo(['The coupon code "test" is not valid.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
