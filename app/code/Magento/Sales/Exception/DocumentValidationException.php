<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Exception\DocumentValidationExceptionInterface;

/**
 * @api
 * @since 100.1.2
 */
class DocumentValidationException extends LocalizedException implements DocumentValidationExceptionInterface
{
}
