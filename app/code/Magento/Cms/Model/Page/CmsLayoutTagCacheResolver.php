<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Get additional layout cache tag for CMS layout.
 */
class CmsLayoutTagCacheResolver implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        if ($this->isExistingPageLayoutChange($object)) {
            return [
                sprintf(
                '%s_%s',
                'CMS_PAGE_VIEW_ID',
                str_replace('-', '_', strtoupper($object->getIdentifier())))
            ];
        }

        return [];
    }

    /**
     * Check if existing CMS page layout change
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isExistingPageLayoutChange(AbstractModel $object): bool
    {
        return !$object instanceof Page ||
            !$object->dataHasChangedFor(Page::PAGE_LAYOUT) ||
            $object->isObjectNew();
    }
}
