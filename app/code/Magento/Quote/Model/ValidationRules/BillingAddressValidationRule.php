<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ValidationRules\AddressValidationRule;

/**
 * @inheritdoc
 */
class BillingAddressValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var AddressValidationRule
     */
    private $addressValidationRule;

    /**
     * Constructor.
     *
     * @param ValidationResultFactory $validationResultFactory
     * @param AddressValidationRule $addressValidationRule
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        AddressValidationRule $addressValidationRule
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->addressValidationRule = $addressValidationRule;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setStoreId($quote->getStoreId());

        $this->addressValidationRule->validateAddress($billingAddress, $validationErrors);

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
