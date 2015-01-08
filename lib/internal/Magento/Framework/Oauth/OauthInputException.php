<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
