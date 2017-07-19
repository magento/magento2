<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

/**
 * @api
 */
class ConfigurationMismatchException extends LocalizedException
{
    /**
     * @deprecated
     */
    const AUTHENTICATION_ERROR = 'Configuration mismatch detected.';
}
