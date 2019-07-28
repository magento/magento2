<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Checkout\Test\TestStep\FillShippingAddressStep;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Signifyd\Test\Fixture\SignifydAddress;

/**
 * Class SignifydFillShippingAddressStep only overrides type of $shippingAddress.
 *
 * We cannot configure Checkout_FillShippingAddressStep this via di.xml,
 * because 'source' handler Firstname, specified in SignifydAddress
 * fixture did not apply when the step was called from testcase.xml scenario.
 *
 * Note. When fixture was called directly in the class constructor,
 * source handlers applied correctly.
 */
class SignifydFillShippingAddressStep extends FillShippingAddressStep
{
    /**
     * @var SignifydAddress|null
     */
    private $signifydAddress;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param SignifydAddress|null $signifydAddress
     * @param null $shippingAddressCustomer
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        SignifydAddress $signifydAddress = null,
        $shippingAddressCustomer = null
    ) {
        parent::__construct(
            $checkoutOnepage,
            $customer,
            $objectManager,
            $fixtureFactory,
            $signifydAddress,
            $shippingAddressCustomer
        );
        $this->signifydAddress = $signifydAddress;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();

        return [
            'signifydAddress' => $this->signifydAddress,
        ];
    }
}
