<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\CatalogUrlRewrite\Plugin\Webapi\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;

/**
 * Plugin for category repository
 *
 * Format url_keys and url_paths before saving the entity.
 */
class FormatUrlKeyAndPath
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
            if ($attribute = $category->getCustomAttribute($attributeKey)) {
                $attribute->setValue($category->formatUrlKey(
                    $category->getData($attributeKey)
                ));
            }
        }
        return [$category];
    }
}
