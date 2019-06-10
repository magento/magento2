<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Exception\LocalizedException;

/**
 * FailureToSendException is thrown when trying to set a cookie but the response has already been sent, making it
 * impossible to send any cookie information back to the client.
 *
 * @api
 * @since 100.0.2
 */
class FailureToSendException extends LocalizedException
{
}
