<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
