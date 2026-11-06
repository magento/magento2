<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Applies "Product Fields Auto-Generation" masks to new products saved through the web API.
 *
 * The admin product form does the same in the browser (Magento_Catalog/js/components/import-handler).
 * Off by default, so that integrations which leave the meta fields empty keep the storefront fallbacks.
 */
class GenerateProductFieldsFromMasks
{
    private const XML_PATH_APPLY_TO_API = 'catalog/fields_masks_api/enabled';

    private const FIELDS = [
        ProductAttributeInterface::CODE_SEO_FIELD_META_TITLE,
        ProductAttributeInterface::CODE_SEO_FIELD_META_KEYWORD,
        ProductAttributeInterface::CODE_SEO_FIELD_META_DESCRIPTION,
    ];

    /**
     * @param ProductHelper $productHelper
     * @param ProductResource $productResource
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ProductHelper $productHelper,
        private readonly ProductResource $productResource,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Fill the fields a new product was sent without
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product,
        $saveOptions = false
    ): ?array {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_APPLY_TO_API)
            || $product->getId()
            || $this->productResource->getIdBySku($product->getSku())
        ) {
            return null;
        }

        $masks = $this->productHelper->getFieldsAutogenerationMasks();
        foreach (self::FIELDS as $field) {
            if (empty($masks[$field]) || $product->getData($field) !== null) {
                continue;
            }
            $value = $this->applyMask((string)$masks[$field], $product);
            if ($value !== null) {
                $product->setCustomAttribute($field, $value);
            }
        }

        return null;
    }

    /**
     * Replace the mask placeholders with product values, null when none of them has a value
     *
     * @param string $mask
     * @param ProductInterface $product
     * @return string|null
     */
    private function applyMask(string $mask, ProductInterface $product): ?string
    {
        $hasValue = false;
        $value = preg_replace_callback(
            '/{{(.*?)}}/',
            function (array $match) use ($product, &$hasValue): string {
                $placeholderValue = $product->getData($match[1]);
                if (!is_scalar($placeholderValue) || (string)$placeholderValue === '') {
                    return '';
                }
                $hasValue = true;

                return (string)$placeholderValue;
            },
            $mask
        );

        if (!$hasValue) {
            return null;
        }
        $value = preg_replace(['/<style.*?>.*?<\/style>/is', '/{{widget.*?}}/is'], '', $value);

        return $value === null ? null : strip_tags($value);
    }
}
