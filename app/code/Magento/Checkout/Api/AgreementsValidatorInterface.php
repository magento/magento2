<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

/**
 * Interface AgreementsValidatorInterface
 * @api
 * @since 2.0.0
 */
interface AgreementsValidatorInterface
{
    /**
     * @param array $agreementIds
     * @return bool
     * @since 2.0.0
     */
    public function isValid($agreementIds = []);
}
