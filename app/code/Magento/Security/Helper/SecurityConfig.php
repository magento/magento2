<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Helper;

use \Magento\Store\Model\ScopeInterface;

/**
 * Security config helper
 */
class SecurityConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Admin area code
     */
    const ADMIN_AREA_SCOPE = 0;

    /**
     * Fronted area code
     */
    const FRONTED_AREA_SCOPE = 1;

    /**
     * Period of time which will be used to calculate all types of limitations (s)
     */
    const TIME_PERIOD_TO_CALCULATE_LIMITATIONS = 3600;

    /**
     * Configuration path to admin area
     */
    const XML_PATH_ADMIN_AREA = 'admin/security/';

    /**
     * Configuration path to fronted area
     */
    const XML_PATH_FRONTED_AREA = 'customer/password/';

    /**
     * Configuration path to admin account sharing
     */
    const XML_PATH_ADMIN_ACCOUNT_SHARING = 'admin/security/admin_account_sharing';

    /**
     * Configuration key to limit password reset requests method
     */
    const XML_PATH_LIMIT_PASSWORD_RESET_REQUESTS_METHOD = 'limit_password_reset_requests_method';

    /**
     * Configuration key to limit number password reset requests
     */
    const XML_PATH_LIMIT_NUMBER_REQUESTS = 'limit_number_password_reset_requests';

    /**
     * Configuration key to limit time between password reset requests
     */
    const XML_PATH_LIMIT_TIME_BETWEEN_REQUESTS = 'limit_time_between_password_reset_requests';

    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';

    /**
     * Get Email of a customer service
     *
     * @return string
     */
    public function getCustomerServiceEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return period of time which will be used to calculate all types of limitations (s)
     *
     * @return int
     */
    public function getTimePeriodToCalculateLimitations()
    {
        return self::TIME_PERIOD_TO_CALCULATE_LIMITATIONS;
    }

    /**
     * Check if admin account sharing enabled
     *
     * @return bool
     */
    public function isAdminAccountSharingEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADMIN_ACCOUNT_SHARING,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Admin Session lifetime setting value
     *
     * @return int
     */
    public function getAdminSessionLifetime()
    {
        return (int) $this->scopeConfig->getValue(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME);
    }

    /**
     * Get xml path by scope
     *
     * @param int $scope
     * @return string
     */
    protected function getXmlPathByScope($scope)
    {
        if ($scope == self::FRONTED_AREA_SCOPE) {
            return self::XML_PATH_FRONTED_AREA;
        } elseif ($scope == self::ADMIN_AREA_SCOPE) {
            return self::XML_PATH_ADMIN_AREA;
        }
    }

    /**
     * Get limit password reset requests method
     *
     * @param int $scope
     * @return int
     */
    public function getLimitPasswordResetRequestsMethod($scope)
    {
        return (int) $this->scopeConfig->getValue(
            $this->getXmlPathByScope($scope) . self::XML_PATH_LIMIT_PASSWORD_RESET_REQUESTS_METHOD,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get limit number password reset requests
     *
     * @param int $scope
     * @return int
     */
    public function getLimitNumberPasswordResetRequests($scope)
    {
        return (int) $this->scopeConfig->getValue(
            $this->getXmlPathByScope($scope) . self::XML_PATH_LIMIT_NUMBER_REQUESTS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get limit time between password reset requests (s)
     *
     * @param int $scope
     * @return int
     */
    public function getLimitTimeBetweenPasswordResetRequests($scope)
    {
        $timeInMin = $this->scopeConfig->getValue(
            $this->getXmlPathByScope($scope) . self::XML_PATH_LIMIT_TIME_BETWEEN_REQUESTS,
            ScopeInterface::SCOPE_STORE
        );
        return $timeInMin * 60;
    }

    /**
     * Get remote user Ip
     *
     * @param bool $ipToLong converting IP to long format
     * @return string|int
     */
    public function getRemoteIp($ipToLong = true)
    {
        return $this->_remoteAddress->getRemoteAddress($ipToLong);
    }

    /**
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return time();
    }
}
