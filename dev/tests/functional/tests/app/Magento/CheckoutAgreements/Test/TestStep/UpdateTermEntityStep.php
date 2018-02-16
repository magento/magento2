<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\TestStep;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementNew;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Update term entity
 */
class UpdateTermEntityStep implements TestStepInterface
{
    /**
     * Updated checkout agreement data.
     *
     * @var CheckoutAgreement
     */
    protected $agreement;
    /**
     * Original checkout agreement data.
     *
     * @var CheckoutAgreement
     */
    protected $agreementUpdated;

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
     * @param CheckoutAgreementIndex $agreementIndex
     * @param CheckoutAgreementNew $agreementNew
     * @param CheckoutAgreement $agreement
     * @param CheckoutAgreement $agreementUpdated
     */
    public function __construct(
        CheckoutAgreementIndex $agreementIndex,
        CheckoutAgreementNew $agreementNew,
        CheckoutAgreement $agreement,
        CheckoutAgreement $agreementUpdated
    ) {
        $this->agreementIndex = $agreementIndex;
        $this->agreementNew = $agreementNew;
        $this->agreement = $agreement;
        $this->agreementUpdated = $agreementUpdated;
    }

    /**
     * Update checkout agreement.
     *
     * @return array
     */
    public function run()
    {
        $this->agreementIndex->open();
        $this->agreementIndex->getAgreementGridBlock()->searchAndOpen(['name' => $this->agreement->getName()]);
        $this->agreementNew->getAgreementsForm()->fill($this->agreementUpdated);
        $this->agreementNew->getPageActionsBlock()->save();
        return ['agreement' => $this->agreementUpdated];
    }
}
