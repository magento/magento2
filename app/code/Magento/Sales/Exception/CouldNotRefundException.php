<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Exception\CouldNotRefundExceptionInterface;

/**
 * @api
 * @since 100.1.3
 */
class CouldNotRefundException extends LocalizedException implements CouldNotRefundExceptionInterface
{
}
