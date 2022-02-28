<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdobeIms\Model\Config;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\UrlInterface;

class ImsConfig extends Config
{
    public const XML_PATH_ENABLED = 'adobe_ims/integration/enabled';
    public const XML_PATH_ORGANIZATION_ID = 'adobe_ims/integration/organization_id';
    public const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';
    public const XML_PATH_PRIVATE_KEY = 'adobe_ims/integration/private_key';
    public const XML_PATH_AUTH_URL_PATTERN = 'adobe_ims/integration/auth_url_pattern';

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
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $url
     * @param WriterInterface $writer
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        WriterInterface $writer,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($scopeConfig, $url);
        $this->writer = $writer;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function enabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED
        );
    }

    /**
     * @param string $path
     * @param string $value
     * @return void
     */
    public function updateConfig(string $path, string $value): void
    {
        $this->writer->save(
            $path,
            $value
        );
    }

    /**
     * @param string $path
     * @param string $value
     * @return void
     */
    public function updateSecureConfig(string $path, string $value): void
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
     * @param string $path
     * @return void
     */
    public function deleteConfig(string $path): void
    {
        $this->writer->delete($path);
    }

    /**
     * Generate the AdminAdobeIms AuthUrl with given clientID or the ClientID stored in the config
     *
     * @return string|null $clientId
     * @return string
     */
    public function getAdminAdobeImsAuthUrl(?string $clientId): string
    {
        if ($clientId === null) {
            $clientId = $this->getApiKey();
        }

        return str_replace(
            ['#{client_id}', '#{redirect_uri}', '#{locale}'],
            [$clientId, $this->getAdminAdobeImsCallBackUrl(), $this->getLocale()],
            $this->scopeConfig->getValue(self::XML_PATH_AUTH_URL_PATTERN)
        );
    }

    /**
     * Get callback url for AdminAdobeIms Module
     *
     * @return string
     */
    private function getAdminAdobeImsCallBackUrl(): string
    {
        return $this->scopeConfig->getValue('web/secure/base_url');
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
}
