<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

/**
 * Represent the Adobe IMS config model responsible for retrieving config settings for Adobe Ims
 */
class Config implements ConfigInterface
{
    private const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';
    private const XML_PATH_PRIVATE_KEY = 'adobe_ims/integration/private_key';
    private const XML_PATH_TOKEN_URL = 'adobe_ims/integration/token_url';
    private const XML_PATH_AUTH_URL_PATTERN = 'adobe_ims/integration/auth_url_pattern';
    private const XML_PATH_LOGOUT_URL_PATTERN = 'adobe_ims/integration/logout_url';
    private const XML_PATH_IMAGE_URL_PATTERN = 'adobe_ims/integration/image_url';
    private const OAUTH_CALLBACK_URL = 'adobe_ims/oauth/callback';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
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
        return $this->scopeConfig->getValue(self::XML_PATH_TOKEN_URL);
    }

    /**
     * @inheritdoc
     */
    public function getAuthUrl(): string
    {
        return str_replace(
            ['#{client_id}', '#{redirect_uri}', '#{locale}'],
            [$this->getApiKey(), $this->getCallBackUrl(), $this->getLocale()],
            $this->scopeConfig->getValue(self::XML_PATH_AUTH_URL_PATTERN)
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
        return str_replace(
            ['#{access_token}', '#{redirect_uri}'],
            [$accessToken, $redirectUrl],
            $this->scopeConfig->getValue(self::XML_PATH_LOGOUT_URL_PATTERN)
        );
    }

    /**
     * @inheritdoc
     */
    public function getProfileImageUrl(): string
    {
        return str_replace(
            ['#{api_key}'],
            [$this->getApiKey()],
            $this->scopeConfig->getValue(self::XML_PATH_IMAGE_URL_PATTERN)
        );
    }
}
