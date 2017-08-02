<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

/**
 * Class AgreementsValidator
 * @since 2.0.0
 */
class AgreementsValidator implements \Magento\Checkout\Api\AgreementsValidatorInterface
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsProviderInterface[]
     * @since 2.0.0
     */
    protected $agreementsProviders;

    /**
     * @param AgreementsProviderInterface[] $list
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
