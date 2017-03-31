<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Security config
 */
class Config implements ConfigInterface
{
    /**
     * Period of time which will be used to limit frequency of password reset requests
     */
    const LIMITATION_TIME_PERIOD = 3600;

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
    const XML_PATH_PASSWORD_RESET_PROTECTION_TYPE = 'password_reset_protection_type';

    /**
     * Configuration key to max number password reset requests
     */
    const XML_PATH_MAX_NUMBER_PASSWORD_RESET_REQUESTS = 'max_number_password_reset_requests';

    /**
     * Configuration key to minimum time between password reset requests
     */
    const XML_PATH_MIN_TIME_BETWEEN_PASSWORD_RESET_REQUESTS = 'min_time_between_password_reset_requests';

    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * SecurityConfig constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeInterface $scope
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ScopeInterface $scope
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->scope = $scope;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getCustomerServiceEmail()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            StoreScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function getLimitationTimePeriod()
    {
        return self::LIMITATION_TIME_PERIOD;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isAdminAccountSharingEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADMIN_ACCOUNT_SHARING,
            StoreScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function getAdminSessionLifetime()
    {
        return (int) $this->scopeConfig->getValue(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    protected function getXmlPathPrefix()
    {
        if ($this->scope->getCurrentScope() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return self::XML_PATH_ADMIN_AREA;
        }
        return self::XML_PATH_FRONTED_AREA;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getPasswordResetProtectionType()
    {
        return (int) $this->scopeConfig->getValue(
            $this->getXmlPathPrefix() . self::XML_PATH_PASSWORD_RESET_PROTECTION_TYPE,
            StoreScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param int $scope
     * @return int
     */
    public function getMaxNumberPasswordResetRequests()
    {
        return (int) $this->scopeConfig->getValue(
            $this->getXmlPathPrefix() . self::XML_PATH_MAX_NUMBER_PASSWORD_RESET_REQUESTS,
            StoreScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param int $scope
     * @return int
     */
    public function getMinTimeBetweenPasswordResetRequests()
    {
        $timeInMin = $this->scopeConfig->getValue(
            $this->getXmlPathPrefix() . self::XML_PATH_MIN_TIME_BETWEEN_PASSWORD_RESET_REQUESTS,
            StoreScopeInterface::SCOPE_STORE
        );
        return $timeInMin * 60;
    }
}
