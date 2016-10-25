<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Vault\Test\Page\StoredPaymentMethods;

class DeleteStoredPaymentStep implements TestStepInterface
{
    /**
     * @var CustomerAccountIndex
     */
    private $customerAccountIndex;

    /**
     * @var StoredPaymentMethods
     */
    private $storedPaymentMethodsPage;

    /**
     * DeleteStoredPaymentStep constructor.
     *
     * @param StoredPaymentMethods $storedPaymentMethodsPage
     * @param CustomerAccountIndex $customerAccountIndex
     */
    public function __construct(
        StoredPaymentMethods $storedPaymentMethodsPage,
        CustomerAccountIndex $customerAccountIndex
    ) {
        $this->storedPaymentMethodsPage = $storedPaymentMethodsPage;
        $this->customerAccountIndex = $customerAccountIndex;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function run()
    {
        $this->customerAccountIndex->open();
        $this->customerAccountIndex->getAccountMenuBlock()->openMenuItem('Stored Payment Methods');
        $storedPaymentsBlock = $this->storedPaymentMethodsPage->getStoredPaymentsBlock();
        $storedPaymentsBlock->deleteStoredPayment();
    }
}
