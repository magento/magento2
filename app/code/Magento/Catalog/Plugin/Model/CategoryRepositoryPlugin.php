<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;

/**
 * Plugin for category repository
 */
class CategoryRepositoryPlugin
{
    private const ATTRIBUTES_TO_PROCESS = [
        'url_key',
        'url_path'
    ];

    /**
     * Formats category url key and path using the default formatter.
     *
     * @param CategoryRepository $subject
     * @param CategoryInterface $category
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CategoryRepository $subject, CategoryInterface $category): array
    {
        foreach (self::ATTRIBUTES_TO_PROCESS as $attributeKey) {
            $attribute = $category->getCustomAttribute($attributeKey);
            if ($attribute !== null) {
                $value = $category->getData($attributeKey);
                $formattedValue = $category->formatUrlKey($value);
                $attribute->setValue($formattedValue);
            }
        }
        return [$category];
    }
}
