<?php

namespace Magento\AdminAdobeIms\Service;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ImsConfig
{
    public const XML_PATH_ENABLED = 'adobe_ims/integration/enabled';
    public const XML_PATH_ORGANIZATION_ID = 'adobe_ims/integration/organization_id';
    public const XML_PATH_API_KEY = 'adobe_ims/integration/api_key';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
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
     * Update config
     *
     * @param string $path
     * @param string $value
     * @return void
     */
    public function updateConfig(string $path, string $value): void
    {
        $this->configWriter->save(
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
     * Delete config value
     *
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
     * @param string|null $clientId
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
