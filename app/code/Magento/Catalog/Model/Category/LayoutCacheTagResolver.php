<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Widget\Model\Widget\Instance;

/**
 * Get additional layout cache tag for category layout.
 */
class LayoutCacheTagResolver implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        if ($this->isExistingCategoryLayoutChange($object)) {
            return [
                str_replace('{{ID}}', (string) $object->getId(), Instance::SINGLE_CATEGORY_LAYOUT_HANDLE)
            ];
        }
        return [];
    }

    /**
     * Check if existing category page layout change
     *
     * @param Category $object
     * @return bool
     */
    private function isExistingCategoryLayoutChange(Category $object): bool
    {
        return !$object->isObjectNew() && $this->isObjectChanged($object);
    }

    /**
     * Check if the page layout of the given category is changed
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isObjectChanged(AbstractModel $object): bool
    {
        $isChanged = false;
        $objectNewPageLayout = $object->getData('page_layout');
        $objectOldPageLayout = $object->getOrigData('page_layout');
        if ($objectNewPageLayout !== 'empty' &&
            $objectNewPageLayout !== $objectOldPageLayout
        ) {
            $isChanged = true;
        }
        return $isChanged;
    }
}
