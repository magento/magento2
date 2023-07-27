<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Output;

use Magento\Catalog\Model\Entity\Attribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\GetAttributeDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Format attributes metadata for GraphQL output
 */
class AttributeMetadata implements GetAttributeDataInterface
{
    /**
     * @var string
     */
    private string $entityType;

    /**
     * @param string $entityType
     */
    public function __construct(string $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * Retrieve formatted attribute data
     *
     * @param Attribute $attribute
     * @param string $entityType
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(
        AttributeInterface $attribute,
        string $entityType,
        int $storeId
    ): array {
        if ($entityType !== $this->entityType) {
            return [];
        }

        $metadata = [
            'is_comparable' => $attribute->getIsComparable() === "1",
            'is_filterable' => $attribute->getIsFilterable() === "1",
            'is_filterable_in_search' => $attribute->getIsFilterableInSearch() === "1",
            'is_searchable' => $attribute->getIsSearchable() === "1",
            'is_html_allowed_on_front' => $attribute->getIsHtmlAllowedOnFront() === "1",
            'is_used_for_price_rules' => $attribute->getIsUsedForPriceRules() === "1",
            'is_used_for_promo_rules' => $attribute->getIsUsedForPromoRules() === "1",
            'is_visible_in_advanced_search' => $attribute->getIsVisibleInAdvancedSearch() === "1",
            'is_visible_on_front' => $attribute->getIsVisibleOnFront() === "1",
            'is_wysiwyg_enabled' => $attribute->getIsWysiwygEnabled() === "1",
            'used_in_product_listing' => $attribute->getUsedInProductListing() === "1",
            'apply_to' => null
        ];

        if (!empty($attribute->getApplyTo())) {
            $metadata['apply_to'] = array_map('strtoupper', $attribute->getApplyTo());
        }

        if (!empty($attribute->getAdditionalData())) {
            $additionalData = json_decode($attribute->getAdditionalData(), true);
            $metadata = array_merge(
                $metadata,
                array_map('strtoupper', $additionalData)
            );
        }

        return $metadata;
    }
}
