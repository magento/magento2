<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;

/**
 * Category Save Plugin updates default sort by
 */
class DefaultSortByUpdateCategorySavePlugin
{
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @var string
     */
    private static $defaultSortByFromKey = 'default_sort_by';

    /**
     * DefaultSortByUpdateCategorySavePlugin constructor.
     *
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter ?? ObjectManager::getInstance()
                ->get(ExtensibleDataObjectConverter::class);
    }

    /**
     * Before save
     *
     * @param CategoryRepositoryInterface $subject
     * @param CategoryInterface $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CategoryRepositoryInterface $subject,
        CategoryInterface $category
    ): void {
        $existingData = $this->extensibleDataObjectConverter
            ->toNestedArray($category, [], CategoryInterface::class);

        if (isset($existingData['default_sort_by']) &&
            is_array($existingData['default_sort_by'])) {
            $category->setCustomAttribute(
                self::$defaultSortByFromKey,
                $existingData['default_sort_by'][0]
            );
        }
    }
}
