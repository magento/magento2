<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Api;

/**
 * Interface AgreementsValidatorInterface
 * @api
 */
interface AgreementsValidatorInterface
{
    /**
     * @param array $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = []);
}
