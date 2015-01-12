<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Oauth;

use Magento\Framework\Exception\InputException;

/**
 * OAuth \OAuthInputException
 */
class OauthInputException extends InputException
{
    /**
     * Get error messages as a single comma separated string
     *
     * @return string
     */
    public function getAggregatedErrorMessage()
    {
        $errors = [];
        foreach ($this->getErrors() as $error) {
            // Clean up any trailing period
            $errors[] = rtrim($error->getMessage(), '.');
        }
        $errorMsg = '';
        if (!empty($errors)) {
            $errorMsg = implode(', ', $errors);
        }
        return $errorMsg;
    }
}
