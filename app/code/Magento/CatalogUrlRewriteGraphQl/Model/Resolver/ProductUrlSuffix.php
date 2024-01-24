<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Model\Resolver;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Returns the url suffix for product
 */
class ProductUrlSuffix implements ResolverInterface, ResetAfterRequestInterface
{
    /**
     * System setting for the url suffix for products
     *
     * @var string
     */
    private static $xml_path_product_url_suffix = 'catalog/seo/product_url_suffix';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    private $productUrlSuffix = [];

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
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?string {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = (int)$store->getId();
        return $this->getProductUrlSuffix($storeId);
    }

    /**
     * Retrieve product url suffix by store
     *
     * @param int $storeId
     * @return string|null
     */
    private function getProductUrlSuffix(int $storeId): ?string
    {
        if (!isset($this->productUrlSuffix[$storeId])) {
            $this->productUrlSuffix[$storeId] = $this->scopeConfig->getValue(
                self::$xml_path_product_url_suffix,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?? '';
        }
        return $this->productUrlSuffix[$storeId];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->productUrlSuffix = [];
    }
}
