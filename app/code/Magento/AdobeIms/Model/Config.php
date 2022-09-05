<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;

/**
 * Represent the Adobe IMS config model responsible for retrieving config settings for Adobe Ims
 */
class Config implements ConfigInterface
{
    private const XML_CONFIG_PATH = 'adobe_ims/integration/';
    public const XML_PATH_ENABLED = 'adobe_ims/integration/admin_enabled';
    private const XML_PATH_ORGANIZATION_ID = 'adobe_ims/integration/organization_id';
    private const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';
    private const XML_PATH_PRIVATE_KEY = 'adobe_ims/integration/private_key';
    private const XML_PATH_TOKEN_URL = 'adobe_ims/integration/token_url';
    private const XML_PATH_AUTH_URL_PATTERN = 'adobe_ims/integration/auth_url_pattern';
    private const XML_PATH_IMAGE_URL_PATTERN = 'adobe_ims/integration/image_url';
    private const OAUTH_CALLBACK_URL = 'adobe_ims/oauth/callback';
    private const XML_PATH_PROFILE_URL = 'adobe_ims/integration/profile_url';
    private const XML_PATH_VALIDATE_TOKEN_URL = 'adobe_ims/integration/validate_token_url';
    private const XML_PATH_ADMIN_AUTH_URL_PATTERN = 'adobe_ims/integration/admin/auth_url_pattern';
    private const XML_PATH_ADMIN_REAUTH_URL_PATTERN = 'adobe_ims/integration/admin/reauth_url_pattern';
    private const OAUTH_CALLBACK_IMS_URL = 'adobe_ims_auth/oauth/';
    private const XML_PATH_ADMIN_ADOBE_IMS_SCOPES = 'adobe_ims/integration/admin/scopes';
    private const XML_PATH_ADOBE_IMS_SCOPES = 'adobe_ims/integration/scopes';
    private const XML_PATH_LOGOUT_URL = 'adobe_ims/integration/logout_url';
    public const XML_PATH_ADMIN_LOGOUT_URL = 'adobe_ims/integration/admin_logout_url';
    private const XML_PATH_CERTIFICATE_PATH = 'adobe_ims/integration/certificate_path';
    private const XML_PATH_ORGANIZATION_MEMBERSHIP_URL = 'adobe_ims/integration/organization_membership_url';
    /**
     * AdminAdobeIms callback urls
     */
    private const IMS_CALLBACK = 'imscallback';
    private const IMS_REAUTH_CALLBACK = 'imsreauthcallback';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

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
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     * @param WriterInterface|null $writer
     * @param EncryptorInterface|null $encryptor
     * @param BackendUrlInterface|null $backendUrl
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        WriterInterface $writer = null,
        EncryptorInterface $encryptor = null,
        BackendUrlInterface $backendUrl = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->writer = $writer ?? ObjectManager::getInstance()
                ->get(WriterInterface::class);
        $this->encryptor = $encryptor ?? ObjectManager::getInstance()
                ->get(EncryptorInterface::class);
        $this->backendUrl = $backendUrl ?? ObjectManager::getInstance()
                ->get(BackendUrlInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getApiKey(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getPrivateKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_PRIVATE_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getTokenUrl(): string
    {
        return str_replace(
            ['#{imsUrl}'],
            [$this->getImsUrl()],
            $this->scopeConfig->getValue(self::XML_PATH_TOKEN_URL)
        );
    }

    /**
     * @inheritdoc
     */
    public function getAuthUrl(): string
    {
        return str_replace(
            ['#{imsUrl}','#{client_id}', '#{redirect_uri}', '#{scope}', '#{locale}'],
            [
                $this->getImsUrl(),
                $this->getApiKey(),
                $this->getCallBackUrl(),
                $this->getScopes(),
                $this->getLocale(),
            ],
            $this->scopeConfig->getValue(self::XML_PATH_AUTH_URL_PATTERN) ?? ''
        );
    }

    /**
     * @inheritdoc
     */
    public function getCallBackUrl(): string
    {
        return $this->url->getUrl(self::OAUTH_CALLBACK_URL);
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
     * @inheritdoc
     */
    public function getLogoutUrl(string $accessToken, string $redirectUrl = '') : string
    {
        // there is no success response with empty redirect url
        if ($redirectUrl === '') {
            $redirectUrl = 'self';
        }
        return str_replace(
            ['#{imsUrl}', '#{access_token}', '#{redirect_uri}'],
            [$this->getImsUrl(), $accessToken, $redirectUrl],
            $this->scopeConfig->getValue(self::XML_PATH_LOGOUT_URL) ?? ''
        );
    }

    /**
     * @inheritdoc
     */
    public function getProfileImageUrl(): string
    {
        return str_replace(
            ['#{imageUrl}', '#{api_key}'],
            [$this->getImsUrl('imageUrl'), $this->getApiKey()],
            $this->scopeConfig->getValue(self::XML_PATH_IMAGE_URL_PATTERN) ?? ''
        );
    }

    /**
     * Get Profile URL
     *
     * @return string
     */
    public function getProfileUrl(): string
    {
        return str_replace(
            ['#{imsUrl}', '#{client_id}'],
            [$this->getImsUrl(), $this->getApiKey()],
            $this->scopeConfig->getValue(self::XML_PATH_PROFILE_URL)
        );
    }

    /**
     * Get Token validation url
     *
     * @param string $code
     * @param string $tokenType
     * @return string
     */
    public function getValidateTokenUrl(string $code, string $tokenType): string
    {
        return str_replace(
            ['#{imsUrl}', '#{token}', '#{client_id}', '#{token_type}'],
            [$this->getImsUrl(), $code, $this->getApiKey(), $tokenType],
            $this->scopeConfig->getValue(self::XML_PATH_VALIDATE_TOKEN_URL)
        );
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
            ['#{imsUrl}', '#{client_id}', '#{redirect_uri}', '#{scope}', '#{locale}'],
            [
                $this->getImsUrl(),
                $clientId,
                $this->getAdminAdobeImsCallBackUrl(),
                $this->getAdminScopes(),
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
            ['#{imsUrl}', '#{client_id}', '#{redirect_uri}', '#{scope}', '#{locale}'],
            [
                $this->getImsUrl(),
                $this->getApiKey(),
                $this->getAdminAdobeImsReAuthCallBackUrl(),
                $this->getAdminScopes(),
                $this->getLocale()
            ],
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_REAUTH_URL_PATTERN)
        );
    }

    /**
     * Get BackendLogout URL
     *
     * @param string $accessToken
     * @return string
     */
    public function getBackendLogoutUrl(string $accessToken) : string
    {
        return str_replace(
            ['#{imsUrl}', '#{access_token}', '#{client_secret}', '#{client_id}'],
            [$this->getImsUrl(), $accessToken, $this->getPrivateKey(), $this->getApiKey()],
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_LOGOUT_URL)
        );
    }

    /**
     * IMS certificate (public key) location retrieval
     *
     * @param string $fileName
     * @return string
     */
    public function getCertificateUrl(string $fileName): string
    {
        return str_replace(
            ['#{certificateUrl}'],
            [$this->getImsUrl('certificateUrl')],
            $this->scopeConfig->getValue(self::XML_PATH_CERTIFICATE_PATH) . $fileName
        );
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
            ['#{organizationMembershipUrl}', '#{org_id}'],
            [$this->getImsUrl('organizationMembershipUrl'), $orgId],
            $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_MEMBERSHIP_URL)
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
            $this->scopeConfig->getValue(self::XML_PATH_ADOBE_IMS_SCOPES)
        );
    }

    /**
     * Get scopes for AdobeIms
     *
     * @return string
     */
    private function getAdminScopes(): string
    {
        return implode(
            ',',
            $this->scopeConfig->getValue(self::XML_PATH_ADMIN_ADOBE_IMS_SCOPES)
        );
    }

    /**
     * Get ims Urls
     *
     * @param string $urlType
     * @return string
     */
    private function getImsUrl(string $urlType = 'imsUrl'): string
    {
        return $this->scopeConfig->getValue(self::XML_CONFIG_PATH . $urlType);
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
     * Get callback url for AdminAdobeIms Module
     *
     * @return string
     */
    private function getAdminAdobeImsCallBackUrl(): string
    {
        return $this->backendUrl->getUrl(
            self::OAUTH_CALLBACK_IMS_URL . self::IMS_CALLBACK
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
            self::OAUTH_CALLBACK_IMS_URL . self::IMS_REAUTH_CALLBACK
        );
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
     * Retrieve Organization Id
     *
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORGANIZATION_ID);
    }
}
