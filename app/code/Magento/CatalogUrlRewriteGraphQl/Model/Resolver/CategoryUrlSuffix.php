<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Model\Resolver;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Returns the url suffix for category
 */
class CategoryUrlSuffix implements ResolverInterface
{
    const XML_PATH_PRODUCT_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    private $cateogryUrlSuffix = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $cateogryUrlSuffix
     */
    public function __construct(ScopeConfigInterface $scopeConfig, array $cateogryUrlSuffix = [])
    {
        $this->cateogryUrlSuffix = $cateogryUrlSuffix;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): string {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int)$store->getId();
        return $this->getProductUrlSuffix($storeId);
    }

    /**
     * Retrieve category url suffix by store
     *
     * @param int $storeId
     * @return string
     */
    private function getProductUrlSuffix(int $storeId): string
    {
        if (!isset($this->cateogryUrlSuffix[$storeId])) {
            $this->cateogryUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::XML_PATH_PRODUCT_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->cateogryUrlSuffix[$storeId];
    }
}
