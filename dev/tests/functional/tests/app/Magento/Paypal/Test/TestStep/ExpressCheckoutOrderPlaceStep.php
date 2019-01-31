<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Paypal\Test\Page\OrderReviewExpress;
use Magento\Sales\Test\Fixture\OrderInjectable;

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
     * Fixture OrderInjectable.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * @param ObjectManager $objectManager
     * @param OrderReviewExpress $orderReviewExpress
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $shipping
     * @param array $prices
     * @param OrderInjectable|null $order
     */
    public function __construct(
        ObjectManager $objectManager,
        OrderReviewExpress $orderReviewExpress,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        FixtureFactory $fixtureFactory,
        array $products = [],
        array $shipping = [],
        array $prices = [],
        OrderInjectable $order = null
    ) {
        $this->objectManager = $objectManager;
        $this->orderReviewExpress = $orderReviewExpress;
        $this->checkoutOnepage = $checkoutOnepage;
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->shipping = $shipping;
        $this->prices = $prices;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
        $this->order = $order;
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
        $data = [
            'entity_id' => ['products' => $this->products]
        ];
        $orderData = $this->order !== null ? $this->order->getData() : [];
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            ['data' => array_merge($data, $orderData)]
        );
        return [
            'orderId' => $this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId(),
            'order' => $order
        ];
    }
}
