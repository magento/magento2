<?php
/**
 * Webapi module exception. Should be used in web API resources implementation.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Exception extends RuntimeException
{
    /**#@+
     * Error HTTP response codes.
     */
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_INTERNAL_ERROR = 500;
    /**#@-*/

    const ORIGINATOR_SENDER = 'Sender';
    const ORIGINATOR_RECEIVER = 'Receiver';

    /**
     * Initialize exception with HTTP code.
     *
     * @param string $message
     * @param int $code
     * @throws InvalidArgumentException
     */
    public function __construct($message, $code)
    {
        /** Only HTTP error codes are allowed. No success or redirect codes must be used. */
        if ($code < 400 || $code > 599) {
            throw new InvalidArgumentException(sprintf('The specified code "%d" is invalid.', $code));
        }
        parent::__construct($message, $code);
    }

    /**
     * Identify exception originator: sender or receiver.
     *
     * @return string
     */
    public function getOriginator()
    {
        return ($this->getCode() < 500) ? self::ORIGINATOR_SENDER : self::ORIGINATOR_RECEIVER;
    }
}
