<?php
/**
 * Authorization service exception
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Exception;

class AuthorizationException extends LocalizedException
{
    const NOT_AUTHORIZED = 'Consumer is not authorized to access %resources';
}
