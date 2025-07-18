<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Get additional layout cache tag for CMS layout.
 */
class LayoutCacheTagResolver implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        if ($this->isExistingPageLayoutChange($object)) {
            return [
                'CMS_PAGE_VIEW_ID_'.
                str_replace('-', '_', strtoupper($object->getIdentifier()))
            ];
        }
        return [];
    }

    /**
     * Check if existing CMS page layout change
     *
     * @param Page $object
     * @return bool
     */
    private function isExistingPageLayoutChange(Page $object): bool
    {
        return !$object->isObjectNew() &&
            $object->dataHasChangedFor(Page::PAGE_LAYOUT);
    }
}
