<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\Multishipping\Test\Page\MultishippingCheckoutOverview;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

/**
 * Process Terms and Conditions checkbox on multiple addresses checkout page.
 */
class CheckTermOnMultishippingStep implements TestStepInterface
{
    /**
     * Multishipping overview page.
     *
     * @var MultishippingCheckoutOverview
     */
    protected $multishippingCheckoutOverview;

    /**
     * Term and conditions checkbox value.
     *
     * @var string
     */
    protected $agreementValue;

    /**
     * @param MultishippingCheckoutOverview $multishippingCheckoutOverview
     * @param string $agreementValue
     */
    public function __construct(
        MultishippingCheckoutOverview $multishippingCheckoutOverview,
        $agreementValue = 'No'
    ) {
        $this->multishippingCheckoutOverview = $multishippingCheckoutOverview;
        $this->agreementValue = $agreementValue;
    }

    /**
     * Process Terms and Conditions checkbox on multiple addresses checkout overview step.
     *
     * @return void
     */
    public function run()
    {
        $this->multishippingCheckoutOverview->getAgreementReview()->setAgreement($this->agreementValue);
    }
}
