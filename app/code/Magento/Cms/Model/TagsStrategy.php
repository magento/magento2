<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model;

use Magento\Framework\App\Cache\Tag\StrategyInterface;

/**
 * Provides the cms layout cache identity to invalidate on layout change.
 */
class TagsStrategy implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getTags($object)
    {
        return [
            sprintf('%s_%s', Page::CACHE_TAG, $object->getId()),
            sprintf(
                '%s_%s', 'CMS_PAGE_VIEW_ID',
                str_replace('-', '_', strtoupper($object->getIdentifier()))
            )
        ];
    }
}
