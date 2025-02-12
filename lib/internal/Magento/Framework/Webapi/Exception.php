<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Framework\Exception\ErrorMessage;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Web API exception should not be used directly by any modules except for Magento_Webapi.
 *
 * During web API requests, all exceptions are converted to this exception,
 * which is then used for proper error response generation.
 *
 * @api
 */
class Exception extends LocalizedException
{
    /**#@+
     * Error HTTP response codes.
     */
    public const HTTP_BAD_REQUEST = 400;

    public const HTTP_UNAUTHORIZED = 401;

    public const HTTP_FORBIDDEN = 403;

    public const HTTP_NOT_FOUND = 404;

    public const HTTP_METHOD_NOT_ALLOWED = 405;

    public const HTTP_NOT_ACCEPTABLE = 406;

    public const HTTP_TOO_MANY_REQUESTS = 429;

    public const HTTP_INTERNAL_ERROR = 500;

    /**#@-*/

    /**#@+
     * Fault codes that are used in SOAP faults.
     */
    public const FAULT_CODE_SENDER = 'Sender';
    public const FAULT_CODE_RECEIVER = 'Receiver';

    /**
     * Optional exception details.
     *
     * @var array
     */
    protected $_details;

    /**
     * HTTP status code associated with current exception.
     *
     * @var int
     */
    protected $_httpCode;

    /**
     * Exception name is used for SOAP faults generation.
     *
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_stackTrace;

    /**
     * List of errors
     *
     * @var null|\Magento\Framework\Exception\LocalizedException[]
     */
    protected $_errors;

    /**
     * Initialize exception with HTTP code.
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param int $code Error code
     * @param int $httpCode
     * @param array $details Additional exception details
     * @param string $name Exception name
     * @param \Magento\Framework\Exception\LocalizedException[]|null $errors Array of errors messages
     * @param string $stackTrace
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Phrase $phrase,
        $code = 0,
        $httpCode = self::HTTP_BAD_REQUEST,
        array $details = [],
        $name = '',
        $errors = null,
        $stackTrace = null
    ) {
        /** Only HTTP error codes are allowed. No success or redirect codes must be used. */
        if ($httpCode < 400 || $httpCode > 599) {
            throw new \InvalidArgumentException(sprintf('The specified HTTP code "%d" is invalid.', $httpCode));
        }
        parent::__construct($phrase, null, $code);
        $this->code = $code;
        $this->_httpCode = $httpCode;
        $this->_details = $details;
        $this->_name = $name;
        $this->_errors = $errors;
        $this->_stackTrace = $stackTrace;
    }

    /**
     * Retrieve current HTTP code.
     *
     * @return int
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    /**
     * Identify exception originator: sender or receiver.
     *
     * @return string
     */
    public function getOriginator()
    {
        return $this->getHttpCode() < 500 ? self::FAULT_CODE_SENDER : self::FAULT_CODE_RECEIVER;
    }

    /**
     * Retrieve exception details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->_details;
    }

    /**
     * Retrieve exception name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retrieve list of errors.
     *
     * @return null|\Magento\Framework\Exception\LocalizedException[]
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Retrieve stack trace string.
     *
     * @return null|string
     */
    public function getStackTrace()
    {
        return $this->_stackTrace;
    }
}
