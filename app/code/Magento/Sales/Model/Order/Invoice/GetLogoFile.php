<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Invoice;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Get Custom Logo File for Invoice HTML print
 */
class GetLogoFile
{
    private const XML_PATH_SALES_IDENTITY_LOGO_HTML = 'sales/identity/logo_html';
    private const LOGO_BASE_DIR = 'sales/store/logo_html/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return Custom Invoice Logo file url if configured in admin
     *
     * @return string|null
     */
    public function execute(): ?string
    {
        $result = null;

        $invoiceLogoPath = $this->getIdentityLogoHtml();
        if ($invoiceLogoPath) {
            $mediaBaseUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
            $result = sprintf('%s%s%s', $mediaBaseUrl, $this->getLogoBaseDir(), $invoiceLogoPath);
        }

        return $result;
    }

    /**
     * Get base directory for Custom Invoice Logo
     *
     * @return string
     */
    private function getLogoBaseDir(): string
    {
        return self::LOGO_BASE_DIR;
    }

    /**
     * Get Admin Configuration for Invoice Logo HTML
     *
     * @return null|string
     */
    private function getIdentityLogoHtml(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALES_IDENTITY_LOGO_HTML,
            ScopeInterface::SCOPE_STORE,
            null
        );
    }
}
