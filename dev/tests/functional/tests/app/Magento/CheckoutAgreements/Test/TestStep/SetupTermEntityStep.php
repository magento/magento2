<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Setup term entity
 */
class SetupTermEntityStep implements TestStepInterface
{
    /**
     * Checkout agreement data.
     *
     * @var CheckoutAgreement
     */
    protected $agreement;

    /**
     * Delete all terms step.
     *
     * @var DeleteAllTermsEntityStep
     */
    protected $deleteAllTermsEntityStep;

    /**
     * @param DeleteAllTermsEntityStep $deleteAllTermsEntityStep
     * @param CheckoutAgreement $agreement
     */
    public function __construct(
        DeleteAllTermsEntityStep $deleteAllTermsEntityStep,
        CheckoutAgreement $agreement
    ) {
        $this->deleteAllTermsEntityStep = $deleteAllTermsEntityStep;
        $this->agreement = $agreement;
    }

    /**
     * Create checkout agreement.
     *
     * @return array
     */
    public function run()
    {
        $this->agreement->persist();
        return ['agreement' => $this->agreement];
    }

    /**
     * Remove all created terms.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->deleteAllTermsEntityStep->run();
    }
}
