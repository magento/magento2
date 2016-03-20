<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Exception\LocalizedException;

/**
 * FailureToSendException is thrown when trying to set a cookie but the response has already been sent, making it
 * impossible to send any cookie information back to the client.
 */
class FailureToSendException extends LocalizedException
{
}
