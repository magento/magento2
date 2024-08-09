<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsCallback;
use Magento\AdminAdobeIms\Controller\Adminhtml\OAuth\ImsReauthCallback;
use Magento\AdobeIms\Model\Config;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;

class ImsConfig extends Config
{
    public const XML_PATH_ENABLED = 'adobe_ims/integration/admin_enabled';
    public const XML_PATH_LOGGING_ENABLED = 'adobe_ims/integration/logging_enabled';
    public const XML_PATH_ORGANIZATION_ID = 'adobe_ims/integration/organization_id';
    public const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';
    public const XML_PATH_PRIVATE_KEY = 'adobe_ims/integration/private_key';
    public const XML_PATH_PROFILE_URL = 'adobe_ims/integration/profile_url';
    public const XML_PATH_NEW_ADMIN_EMAIL_TEMPLATE = 'adobe_ims/email/content_template';
    public const XML_PATH_VALIDATE_TOKEN_URL = 'adobe_ims/integration/validate_token_url';
    public const XML_PATH_ADMIN_LOGOUT_URL = 'adobe_ims/integration/admin_logout_url';
    public const XML_PATH_CERTIFICATE_PATH = 'adobe_ims/integration/certificate_path';
    public const XML_PATH_ADMIN_AUTH_URL_PATTERN = 'adobe_ims/integration/admin/auth_url_pattern';
    public const XML_PATH_ADMIN_REAUTH_URL_PATTERN = 'adobe_ims/integration/admin/reauth_url_pattern';
    public const XML_PATH_ADMIN_ADOBE_IMS_SCOPES = 'adobe_ims/integration/admin/scopes';
    public const XML_PATH_ORGANIZATION_MEMBERSHIP_URL = 'adobe_ims/integration/organization_membership_url';

    private const OAUTH_CALLBACK_URL = 'adobe_ims_auth/oauth/';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var WriterInterface
     */
    private WriterInterface $writer;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var BackendUrlInterface
     */
    private BackendUrlInterface $backendUrl;

    /**
     * @var FormKey
     */
    private FormKey $formKey;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     * @param WriterInterface $writer
     * @param EncryptorInterface $encryptor
     * @param BackendUrlInterface $backendUrl
     * @param FormKey $formKey
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        WriterInterface $writer,
        EncryptorInterface $encryptor,
        BackendUrlInterface $backendUrl,
        FormKey $formKey
    ) {
        parent::__construct($scopeConfig, $url);
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->backendUrl = $backendUrl;
        $this->formKey = $formKey;
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED
        );
    }

    /**
     * Check if module error-logging is enabled
     *
     * @return bool
     */
    public function loggingEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_LOGGING_ENABLED
        );
    }

    /**
     * Enable Admin Adobe IMS Module and set Client ID and Client Secret and Organization ID and Two Factor Enabled
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $organizationId
     * @param bool $isAdobeIms2FAEnabled
     * @return void
     * @throws LocalizedException
     */
    public function enableModule(
        string $clientId,
        string $clientSecret,
        string $organizationId,
        bool $isAdobeIms2FAEnabled
    ): void {
        if (!$isAdobeIms2FAEnabled) {
            throw new LocalizedException(
                __('2FA is required when enabling the Admin Adobe IMS Module')
            );
        }

        $this->updateConfig(
            self::XML_PATH_ENABLED,
            '1'
        );

        $this->updateSecureConfig(
            self::XML_PATH_ORGANIZATION_ID,
            $organizationId
        );

        $this->updateSecureConfig(
            self::XML_PATH_API_KEY,
            $clientId
        );

        $this->updateSecureConfig(
            self::XML_PATH_PRIVATE_KEY,
            $clientSecret
        );
    }

    /**
     * Disable Admin Adobe IMS Module and unset Client ID and Client Secret from config
     *
     * @return void
     */
    public function disableModule(): void
    {
        $this->updateConfig(
            self::XML_PATH_ENABLED,
            '0'
        );

        $this->deleteConfig(self::XML_PATH_ORGANIZATION_ID);
        $this->deleteConfig(self::XML_PATH_API_KEY);
        $this->deleteConfig(self::XML_PATH_PRIVATE_KEY);
    }

    /**
     * Get Profile URL
     *
     * @return string
     */
    public function getProfileUrl(): string
    {
        return str_replace(
            ['#{client_id}'],
            [$this->getApiKey()],
            $this->scopeConfig->getValue(self::XML_PATH_PROFILE_URL)
        );
    }

    /**
     * Get Token validation url
     *
     * @return string
     */
    public function getValidateTokenUrl(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VALIDATE_TOKEN_URL);
    }

    /**
     * Update config using config writer
     *
     * @param string $path
     * @param string $value
     * @return void
     */
    private function updateConfig(string $path, string $value): void
    {
        $this->writer->save(
            $path,
            $value
        );
    }

    /**
     * Update encrypted config setting
     *
     * @param string $path
     * @param string $value
     * @return void
     */
    private function updateSecureConfig(string $path, string $value): void
    {
        $value = str_replace(['\n', '\r'], ["\n", "\r"], $value);

        if (!preg_match('/^\*+$/', $value) && !empty($value)) {
            $value = $this->encryptor->encrypt($value);

            $this->writer->save(
                $path,
                $value
            );
        }
    }

    /**
     * Delete config value
     *
     * @param string $path
     * @return void
     */
    private function deleteConfig(string $path): void
    {
        $this->writer->delete($path);
    }

    /**
     * Generate the AdminAdobeIms AuthUrl with given clientID or the ClientID stored in the config
     *
     * @param string|null $clientId
     * @return string
     */
    public function getAdminAdobeImsAuthUrl(?string $clientId): string
    {
        if ($clientId === null) {
            $clientId = $this->getApiKey();
        }

        return str_replace(
            ['#{client_id}', '#{redirect_uri}', '#{scope}', '#{state}', '#{locale}'],
            [
                $clientId,
                $this->getAdminAdobeImsCallBackUrl(),
                $this->getScopes(),
                $this->formKey->getFormKey(),
                $this->getLocale()
            ],
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_AUTH_URL_PATTERN)
        );
    }

    /**
     * Generate the AdminAdobeIms AuthUrl for reAuth
     *
     * @return string
     */
    public function getAdminAdobeImsReAuthUrl(): string
    {
        return str_replace(
            ['#{client_id}', '#{redirect_uri}', '#{scope}', '#{state}', '#{locale}'],
            [
                $this->getApiKey(),
                $this->getAdminAdobeImsReAuthCallBackUrl(),
                $this->getScopes(),
                $this->formKey->getFormKey(),
                $this->getLocale()
            ],
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_REAUTH_URL_PATTERN)
        );
    }

    /**
     * Get scopes for AdobeIms
     *
     * @return string
     */
    private function getScopes(): string
    {
        return implode(
            ',',
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_ADOBE_IMS_SCOPES)
        );
    }

    /**
     * Get email template for new created admin users
     *
     * @return string
     */
    public function getEmailTemplateForNewAdminUsers(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_NEW_ADMIN_EMAIL_TEMPLATE
        );
    }

    /**
     * Get callback url for AdminAdobeIms Module
     *
     * @return string
     */
    private function getAdminAdobeImsCallBackUrl(): string
    {
        return $this->backendUrl->getUrl(
            self::OAUTH_CALLBACK_URL . ImsCallback::ACTION_NAME
        );
    }

    /**
     * Get reAuth callback url for AdminAdobeIms Module
     *
     * @return string
     */
    private function getAdminAdobeImsReAuthCallBackUrl(): string
    {
        return $this->backendUrl->getUrl(
            self::OAUTH_CALLBACK_URL . ImsReauthCallback::ACTION_NAME
        );
    }

    /**
     * Get locale
     *
     * @return string
     */
    private function getLocale(): string
    {
        return $this->scopeConfig->getValue(Custom::XML_PATH_GENERAL_LOCALE_CODE);
    }

    /**
     * Get BackendLogout URL
     *
     * @return string
     */
    public function getBackendLogoutUrl() : string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ADMIN_LOGOUT_URL);
    }

    /**
     * Retrieve Organization Id
     *
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_ID);
    }

    /**
     * IMS certificate (public key) location retrieval
     *
     * @param string $fileName
     * @return string
     */
    public function getCertificateUrl(string $fileName): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CERTIFICATE_PATH) . $fileName;
    }

    /**
     * Get url to check organization membership
     *
     * @param string $orgId
     * @return string
     */
    public function getOrganizationMembershipUrl(string $orgId): string
    {
        return str_replace(
            ['#{org_id}'],
            [$orgId],
            $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_MEMBERSHIP_URL)
        );
    }
}
