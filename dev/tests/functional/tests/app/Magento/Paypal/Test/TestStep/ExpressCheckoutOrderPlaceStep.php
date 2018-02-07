<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Paypal\Test\Page\OrderReviewExpress;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;

/**
 * Place order on Magento side after redirecting from PayPal.
 */
class ExpressCheckoutOrderPlaceStep implements TestStepInterface
{
    /**
     * ObjectManager object.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * One page checkout success page.
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * Order Review page on Magento side after redirecting from PayPal.
     *
     * @var OrderReviewExpress
     */
    protected $orderReviewExpress;

    /**
     * Shipping carrier and method.
     *
     * @var array
     */
    protected $shipping;

    /**
     * Order prices.
     *
     * @var array
     */
    protected $prices;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var array
     */
    private $products;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param OrderReviewExpress $orderReviewExpress
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $shipping
     * @param array $prices
     */
    public function __construct(
        ObjectManager $objectManager,
        OrderReviewExpress $orderReviewExpress,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        array $products = [],
        array $shipping = [],
        array $prices = []
    ) {
        $this->objectManager = $objectManager;
        $this->orderReviewExpress = $orderReviewExpress;
        $this->checkoutOnepage = $checkoutOnepage;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->shipping = $shipping;
        $this->prices = $prices;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
    }

    /**
     * Review order contents and place order.
     *
     * @return array
     */
    public function run()
    {
        $this->orderReviewExpress->getReviewBlock()->selectShippingMethod($this->shipping);
        foreach ($this->prices as $priceName => $value) {
            $assertName = 'Assert' . ucfirst($priceName) . 'OrderReview';
            $assert = $this->objectManager->create('Magento\\Checkout\\Test\\Constraint\\' . $assertName);
            $assert->processAssert($this->checkoutOnepage, $value);
        }
        $this->orderReviewExpress->getReviewBlock()->placeOrder();
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'data' => [
                    'entity_id' => ['products' => $this->products]
                ]
            ]
        );
        return [
            'orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId(),
            'order' => $order
        ];
    }
}
