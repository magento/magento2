<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Token;

use Magento\Integration\Model\Oauth\Token\RequestLog\ReaderInterface as RequestLogReader;
use Magento\Integration\Model\Oauth\Token\RequestLog\WriterInterface as RequestLogWriter;
use Magento\Integration\Model\Oauth\Token\RequestLog\Config as RequestLogConfig;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Model for OAuth admin/customer token requests throttling.
 * @since 2.0.3
 */
class RequestThrottler
{
    /**#@+
     * Web API user type
     */
    const USER_TYPE_CUSTOMER = 2;
    const USER_TYPE_ADMIN = 3;
    /**#@-*/

    /**#@-*/
    private $requestLogReader;

    /**
     * @var RequestLogWriter
     * @since 2.0.3
     */
    private $requestLogWriter;

    /**
     * @var RequestLogConfig
     * @since 2.0.3
     */
    private $requestLogConfig;

    /**
     * Initialize dependencies.
     *
     * @param RequestLogReader $requestLogReader
     * @param RequestLogWriter $requestLogWriter
     * @param RequestLogConfig $requestLogConfig
     * @since 2.0.3
     */
    public function __construct(
        RequestLogReader $requestLogReader,
        RequestLogWriter $requestLogWriter,
        RequestLogConfig $requestLogConfig
    ) {
        $this->requestLogReader = $requestLogReader;
        $this->requestLogWriter = $requestLogWriter;
        $this->requestLogConfig = $requestLogConfig;
    }

    /**
     * Throw exception if user account is currently locked because of too many failed authentication attempts.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     * @throws AuthenticationException
     * @since 2.0.3
     */
    public function throttle($userName, $userType)
    {
        $count = $this->requestLogReader->getFailuresCount($userName, $userType);
        if ($count >= $this->requestLogConfig->getMaxFailuresCount()) {
            throw new AuthenticationException(
                __('You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    /**
     * Reset count of failed authentication attempts.
     *
     * Unlock user account and make generation of OAuth tokens possible for this account again.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     * @since 2.0.3
     */
    public function resetAuthenticationFailuresCount($userName, $userType)
    {
        $this->requestLogWriter->resetFailuresCount($userName, $userType);
    }

    /**
     * Increment authentication failures count and lock user account if the limit is reached.
     *
     * Account will be locked until lock expires.
     *
     * @param string $userName
     * @param int $userType
     * @return void
     * @since 2.0.3
     */
    public function logAuthenticationFailure($userName, $userType)
    {
        $this->requestLogWriter->incrementFailuresCount($userName, $userType);
    }
}
