<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementNew;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create term entity
 */
class CreateTermEntityStep implements TestStepInterface
{
    /**
     * Checkout agreement data.
     *
     * @var CheckoutAgreement
     */
    protected $agreement;

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
     * Delete all terms step.
     *
     * @var DeleteAllTermsEntityStep
     */
    protected $deleteAllTermsEntityStep;

    /**
     * @param DeleteAllTermsEntityStep $deleteAllTermsEntityStep
     * @param CheckoutAgreementIndex $agreementIndex
     * @param CheckoutAgreementNew $agreementNew
     * @param CheckoutAgreement $agreement
     */
    public function __construct(
        DeleteAllTermsEntityStep $deleteAllTermsEntityStep,
        CheckoutAgreementIndex $agreementIndex,
        CheckoutAgreementNew $agreementNew,
        CheckoutAgreement $agreement
    ) {
        $this->deleteAllTermsEntityStep = $deleteAllTermsEntityStep;
        $this->agreementIndex = $agreementIndex;
        $this->agreementNew = $agreementNew;
        $this->agreement = $agreement;
    }

    /**
     * Create checkout agreement.
     *
     * @return array
     */
    public function run()
    {
        $this->agreementIndex->open();
        $this->agreementIndex->getPageActionsBlock()->addNew();
        $this->agreementNew->getAgreementsForm()->fill($this->agreement);
        $this->agreementNew->getPageActionsBlock()->save();
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
