<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogUrlRewrite\Block\UrlKeyRenderer;
use Magento\Store\Model\ScopeInterface;

/**
 * Category tab attributes
 */
class Attributes
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
     * Adds attributes meta if url_key exist
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetAttributesMeta(DataProvider $subject, $result)
    {
        if (!isset($result['url_key'])) {
            return $result;
        }

        $category = $subject->getCurrentCategory();
        if ($category && $category->getId()) {
            if ((int) $category->getLevel() === 1) {
                $result['url_key_group']['componentDisabled'] = true;
            } else {
                $result['url_key_create_redirect'] = $this->getUrlRewriteMeta($category);
            }
        } else {
            $result['url_key_create_redirect']['visible'] = false;
        }

        return $result;
    }

    /**
     * Returns url rewrite meta
     *
     * @param CategoryInterface $category
     * @return array
     */
    private function getUrlRewriteMeta(CategoryInterface $category): array
    {
        return [
            'value' => $this->isSaveRewriteHistory($category->getStoreId()) ? $category->getUrlKey() : '',
            'valueMap' => [
                'false' => '',
                'true' => $category->getUrlKey()
            ],
            'disabled' => true,
        ];
    }

    /**
     * Returns Create Permanent Redirect for URLs if changed config enabled
     *
     * @param int $storeId
     * @return bool
     */
    private function isSaveRewriteHistory(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            UrlKeyRenderer::XML_PATH_SEO_SAVE_HISTORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
