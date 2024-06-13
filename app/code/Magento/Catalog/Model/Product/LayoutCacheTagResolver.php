<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Widget\Model\Widget\Instance;

/**
 * Get additional layout cache tag for product layout.
 */
class LayoutCacheTagResolver implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        if ($this->isExistingProductLayoutChange($object)) {
            return [
                str_replace('{{ID}}', (string) $object->getId(), Instance::SINGLE_PRODUCT_LAYOUT_HANDLE)
            ];
        }
        return [];
    }

    /**
     * Check if existing Product page layout change
     *
     * @param Product $object
     * @return bool
     */
    private function isExistingProductLayoutChange(Product $object): bool
    {
        return !$object->isObjectNew() && $this->isObjectChanged($object);
    }

    /**
     * Check if the page layout of the given product is changed
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
