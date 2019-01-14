<?php

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\UrlConfig\UrlConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class UrlConfig implements UrlConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function useCategoryInUrl($storeId)
    {
        return $this->scopeConfig->getValue(
            ProductHelper::XML_PATH_PRODUCT_URL_USE_CATEGORY,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }
}
