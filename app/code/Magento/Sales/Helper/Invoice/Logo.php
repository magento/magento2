<?php

namespace Magento\Sales\Helper\Invoice;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

class Logo extends AbstractHelper
{
    const XML_PATH_SALES_IDENTITY_LOGO_HTML = 'sales/identity/logo_html';

    protected $logoBaseDir = 'sales/store/logo_html/';

    /**
     * @return string|null
     */
    public function getLogoFile()
    {
        $result = null;

        $invoiceLogoPath = $this->getIdentityLogoHtml();
        if ($invoiceLogoPath) {
            $result = $this->_urlBuilder->getBaseUrl(
                ['_type' => UrlInterface::URL_TYPE_MEDIA]
            ) . $this->getLogoBaseDir() . $invoiceLogoPath;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getLogoBaseDir()
    {
        return $this->logoBaseDir;
    }

    /**
     * @param null|int|string $store
     * @return mixed
     */
    public function getIdentityLogoHtml($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALES_IDENTITY_LOGO_HTML,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
