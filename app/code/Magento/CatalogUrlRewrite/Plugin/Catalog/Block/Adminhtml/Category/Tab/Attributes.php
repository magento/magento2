<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

/**
 * Class Attributes
 */
class Attributes
{
    /**
     * XML path for category rewrites history
     */
    const XML_PATH_CATEGORY_REWRITES_HISTORY = 'catalog/seo/save_rewrites_history';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Plugin for url key create redirect
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array                                        $result
     *
     * @return array
     */
    public function afterGetAttributesMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        $result
    ) {

        /**
         * @var \Magento\Catalog\Model\Category $category
         */
        $category = $subject->getCurrentCategory();
        if (isset($result['url_key'])) {
            if ($category && $category->getId()) {
                if ($category->getLevel() == 1) {
                    $result['url_key_group']['componentDisabled'] = true;
                } else {
                    $storeId = $this->storeManager->getStore()->getId();
                    $categoryRewritesHistory = (bool)$this->scopeConfig->getValue(
                        self::XML_PATH_CATEGORY_REWRITES_HISTORY,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );

                    $result['url_key_create_redirect']['valueMap']['true'] =
                        $categoryRewritesHistory ? $category->getUrlKey() : false;
                    $result['url_key_create_redirect']['value'] = $category->getUrlKey();
                    $result['url_key_create_redirect']['disabled'] = true;
                }
            } else {
                $result['url_key_create_redirect']['visible'] = false;
            }
        }
        return $result;
    }
}
