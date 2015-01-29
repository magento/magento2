<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create term entity
 */
class CreateTermEntityStep implements TestStepInterface
{
    /**
     * Checkout agreement page.
     *
     * @var CheckoutAgreement
     */
    protected $agreement;

    /**
     * @param CheckoutAgreement $agreement
     */
    public function __construct(CheckoutAgreement $agreement)
    {
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
}
