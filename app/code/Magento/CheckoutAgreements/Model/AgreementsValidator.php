<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * Class AgreementsValidator
 */
class AgreementsValidator implements \Magento\Checkout\Api\AgreementsValidatorInterface
{
    /** @var  AgreementsProviderInterface[] */
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
            $requiredAgreements = array_merge($requiredAgreements, $agreementsProvider->getRequiredAgreementIds());
        }
        $agreementsDiff = array_diff($requiredAgreements, $agreementIds);
        return empty($agreementsDiff);
    }
}
