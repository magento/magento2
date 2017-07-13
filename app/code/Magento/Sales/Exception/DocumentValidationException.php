<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Exception\DocumentValidationExceptionInterface;

/**
 * @api
 */
class DocumentValidationException extends LocalizedException implements DocumentValidationExceptionInterface
{
}
