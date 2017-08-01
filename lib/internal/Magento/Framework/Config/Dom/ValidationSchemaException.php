<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Exception that should be thrown by DOM model when incoming xsd is not valid.
 */
namespace Magento\Framework\Config\Dom;

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 2.2.0
 */
class ValidationSchemaException extends LocalizedException
{
}
