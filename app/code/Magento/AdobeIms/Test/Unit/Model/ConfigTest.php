<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Test\Unit\Model;

use Magento\AdobeIms\Model\Config;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * PHPUnit test for \Magento\AdobeIms\Model\Config
 */
class ConfigTest extends TestCase
{
    private const XML_CONFIG_PATH = 'adobe_ims/integration/';
    /**
     * API key constants
     */
    private const API_KEY = 'API_KEY';
    private const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';

    /**
     * Private key constants
     */
    private const PRIVATE_KEY = 'PRIVATE_KEY';
    private const XML_PATH_PRIVATE_KEY = 'adobe_ims/integration/private_key';

    /**
     * Token URL constants
     */
    private const TOKEN_URL = 'https://token-url.com/integration';
    private const XML_PATH_TOKEN_URL = 'adobe_ims/integration/token_url';

    /**
     * Auth URL constants
     */
    private const LOCALE_CODE = 'en_US';
    private const XML_PATH_AUTH_URL_PATTERN = 'adobe_ims/integration/auth_url_pattern';
    private const AUTH_URL = 'https://auth-url.com/pattern';
    private const AUTH_URL_PATTERN = 'https://auth-url.com/pattern' .
    '?client_id=#{client_id}&redirect_uri=#{redirect_uri}&locale=#{locale}';

    /**
     * Callback URL constant
     */
    private const CALLBACK_URL = 'https://magento-instance.com/adobe_ims/oauth/callback';

    /**
     * Logout URL constants
     */
    private const XML_PATH_LOGOUT_URL_PATTERN = 'adobe_ims/integration/logout_url';
    private const LOGOUT_URL = 'https://logout-url.com/pattern';
    private const LOGOUT_URL_PATTERN = 'https://logout-url.com/pattern' .
    '?access_token=#{access_token}&redirect_uri=#{redirect_uri}';
    private const REDIRECT_URI = 'REDIRECT_URI';
    private const ACCCESS_TOKEN = 'ACCCESS_TOKEN';

    /**
     * Profile image URL constants
     */
    private const XML_PATH_IMAGE_URL_PATTERN = 'adobe_ims/integration/image_url';
    private const IMAGE_URL_PATTERN = 'https://image-url.com/pattern?api_key=#{api_key}';
    private const IMAGE_URL = 'https://image-url.com/pattern';

    /**
     * Default profile image URL constants
     */
    private const XML_PATH_DEFAULT_PROFILE_IMAGE = 'adobe_ims/integration/default_profile_image';
    private const IMAGE_URL_DEFAULT = 'https://image-url.com/default';

    private const XML_PATH_ADOBE_IMS_SCOPES = 'adobe_ims/integration/scopes';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * Set up test mock objects
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->urlMock = $this->createMock(UrlInterface::class);

        $this->config = new Config($this->scopeConfigMock, $this->urlMock);
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getApiKey
     */
    public function testGetApiKey(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->with(self::XML_PATH_API_KEY)
            ->willReturn(self::API_KEY);

        $this->assertEquals(self::API_KEY, $this->config->getApiKey());
    }

    /**
     * Test for \Magento\AdobeIms\Model\self::getPrivateKey
     */
    public function testGetPrivateKey(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->with(self::XML_PATH_PRIVATE_KEY)
            ->willReturn(self::PRIVATE_KEY);

        $this->assertEquals(self::PRIVATE_KEY, $this->config->getPrivateKey());
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getTokenUrl
     */
    public function testGetTokenUrl(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    self::XML_PATH_TOKEN_URL, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::TOKEN_URL
                ],
                [
                    self::XML_CONFIG_PATH . 'imsUrl', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::TOKEN_URL
                ],
            ]);

        $this->assertEquals(self::TOKEN_URL, $this->config->getTokenUrl());
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getAuthUrl
     */
    public function testGetAuthUrl(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    self::XML_CONFIG_PATH . 'imsUrl', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::AUTH_URL
                ],
                [
                    self::XML_PATH_API_KEY, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::API_KEY
                ],
                [
                    self::XML_PATH_ADOBE_IMS_SCOPES , ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    ['openid']
                ],
                [
                    Custom::XML_PATH_GENERAL_LOCALE_CODE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::LOCALE_CODE
                ],
                [
                    self::XML_PATH_AUTH_URL_PATTERN, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::AUTH_URL_PATTERN
                ]
            ]);

        $this->urlMock->method('getUrl')->willReturn(self::CALLBACK_URL);

        $this->assertEquals(
            'https://auth-url.com/pattern?client_id=' . self::API_KEY .
                '&redirect_uri=' . self::CALLBACK_URL .
                '&locale=' . self::LOCALE_CODE,
            $this->config->getAuthUrl()
        );
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getCallBackUrl
     */
    public function testGetCallBackUrl(): void
    {
        $this->urlMock->method('getUrl')
            ->with('adobe_ims/oauth/callback')
            ->willReturn(self::CALLBACK_URL);

        $this->assertEquals(self::CALLBACK_URL, $this->config->getCallBackUrl());
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getLogoutUrl
     */
    public function testGetLogoutUrl(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    self::XML_PATH_LOGOUT_URL_PATTERN, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::LOGOUT_URL_PATTERN
                ],
                [
                    self::XML_CONFIG_PATH . 'imsUrl', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::LOGOUT_URL
                ],
            ]);

        $this->assertEquals(
            'https://logout-url.com/pattern?access_token=' . self::ACCCESS_TOKEN .
                '&redirect_uri=' . self::REDIRECT_URI,
            $this->config->getLogoutUrl(self::ACCCESS_TOKEN, self::REDIRECT_URI)
        );
    }

    /**
     * Test for \Magento\AdobeIms\Model\Config::getProfileImageUrl
     */
    public function testGetProfileImageUrl(): void
    {
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap([
                [
                    self::XML_CONFIG_PATH . 'imageUrl', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::IMAGE_URL
                ],
                [
                    self::XML_PATH_IMAGE_URL_PATTERN, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::IMAGE_URL_PATTERN
                ],
                [
                    self::XML_PATH_API_KEY, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null,
                    self::API_KEY
                ]
            ]);

        $this->assertEquals(
            'https://image-url.com/pattern?api_key=' . self::API_KEY,
            $this->config->getProfileImageUrl()
        );
    }
}
