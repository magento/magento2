<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Agreements;

/**
 * Class AgreementsValidator
 */
class AgreementsValidator
{
    /** @var  AgreementsProviderInterface[] */
    protected $agreementsProviders;

    /**
     * @param AgreementsProviderInterface[] $list
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
    public function isValid($agreementIds)
    {
        $requiredAgreements = [];
        foreach ($this->agreementsProviders as $agreementsProvider) {
            $requiredAgreements = array_merge($requiredAgreements, $agreementsProvider->getRequiredAgreementIds());
        }
        $agreementsDiff = array_diff($requiredAgreements, $agreementIds);
        return empty($agreementsDiff);
    }
}
