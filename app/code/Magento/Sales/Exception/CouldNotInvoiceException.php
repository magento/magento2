<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Exception\CouldNotInvoiceExceptionInterface;

/**
 * Class CouldNotInvoiceException
 *
 * @api
 * @since 100.1.2
 */
class CouldNotInvoiceException extends LocalizedException implements CouldNotInvoiceExceptionInterface
{
}
