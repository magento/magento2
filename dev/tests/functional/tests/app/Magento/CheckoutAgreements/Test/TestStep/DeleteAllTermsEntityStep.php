<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementNew;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class DeleteAllTermsEntityStep
 * Delete all terms on backend
 */
class DeleteAllTermsEntityStep implements TestStepInterface
{
    /**
     * Checkout agreement index page
     *
     * @var CheckoutAgreementIndex
     */
    protected $agreementIndex;

    /**
     * Checkout agreement new and edit page
     *
     * @var CheckoutAgreementNew
     */
    protected $agreementNew;

    /**
     * @construct
     * @param CheckoutAgreementNew $agreementNew
     * @param CheckoutAgreementIndex $agreementIndex
     */
    public function __construct(
        CheckoutAgreementNew $agreementNew,
        CheckoutAgreementIndex $agreementIndex
    ) {
        $this->agreementNew = $agreementNew;
        $this->agreementIndex = $agreementIndex;
    }

    /**
     * Delete terms on backend
     *
     * @return void
     */
    public function run()
    {
        $this->agreementIndex->open();
        $this->agreementIndex->getAgreementGridBlock()->resetFilter();
        while ($this->agreementIndex->getAgreementGridBlock()->isFirstRowVisible()) {
            $this->agreementIndex->getAgreementGridBlock()->openFirstRow();
            $this->agreementNew->getPageActionsBlock()->delete();
            $this->agreementNew->getModalBlock()->acceptAlert();
        }
    }
}
