<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Plugin\Model\Resolver;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\Resolver\EntityUrl;

/**
 * Validation for product status for url resolver
 */
class EntityUrlExcludeDisabledProductPlugin
{
    /**
     * Constant for product type
     */
    private const TYPE_PRODUCT = 'product';

    /**
     * @var array
     */
    private array $statusCache = [];

    /**
     * @var ProductResource
     */
    private ProductResource $productResource;

    /**
     * @param ProductResource $productResource
     */
    public function __construct(
        ProductResource $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * After plugin for EntityUrl resolver to exclude disabled products
     *
     * @param EntityUrl $subject
     * @param array|null $result
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        EntityUrl $subject,
        ?array $result,
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?array {
        if ($result === null || strtolower($result['type'] ?? '') !== self::TYPE_PRODUCT || empty($result['id'])) {
            return $result;
        }
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cacheKey = $result['id'] . '_' . $storeId;
        if (!array_key_exists($cacheKey, $this->statusCache)) {
            $this->statusCache[$cacheKey] = $this->productResource->getAttributeRawValue(
                (int)$result['id'],
                'status',
                $storeId
            );
        }
        $status = $this->statusCache[$cacheKey];
        if ($status === false || (int)$status !== Status::STATUS_ENABLED) {
            return null;
        }
        return $result;
    }
}
