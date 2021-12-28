<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Checkout\Api\AgreementsValidatorInterface;

/**
 * Validator for Checkout Agreements
 */
class AgreementsValidator implements AgreementsValidatorInterface
{
    /**
     * @var AgreementsProviderInterface[]
     */
    protected $agreementsProviders;

    /**
     * @param AgreementsProviderInterface[] $list
     * @codeCoverageIgnore
     */
    public function __construct($list = null)
    {
        $this->agreementsProviders = (array) $list;
    }

    /**
     * Validate that all required agreements is signed
     *
     * @param int[] $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = [])
    {
        $agreementIds = $agreementIds === null ? [] : $agreementIds;
        $requiredAgreements = [];
        foreach ($this->agreementsProviders as $agreementsProvider) {
            $requiredAgreements[] = $agreementsProvider->getRequiredAgreementIds();
        }

        $agreementsDiff = array_diff(array_merge([], ...$requiredAgreements), $agreementIds);

        return empty($agreementsDiff);
    }
}
