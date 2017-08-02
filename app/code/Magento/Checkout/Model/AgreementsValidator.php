<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * Class AgreementsValidator
 * @since 2.0.0
 */
class AgreementsValidator implements \Magento\Checkout\Api\AgreementsValidatorInterface
{
    /**
     * Default validator
     *
     * @param int[] $agreementIds
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isValid($agreementIds = [])
    {
        return true;
    }
}
