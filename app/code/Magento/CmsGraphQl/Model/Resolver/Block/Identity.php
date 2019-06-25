<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved CMS block
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Magento\Cms\Model\Block::CACHE_TAG;

    /**
     * Get block identities from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            if (is_array($item) && !empty($item[BlockInterface::BLOCK_ID])) {
                $ids[] = sprintf('%s_%s', $this->cacheTag, $item[BlockInterface::BLOCK_ID]);
                $ids[] = sprintf('%s_%s', $this->cacheTag, $item[BlockInterface::IDENTIFIER]);
            }
        }

        if (!empty($ids)) {
            array_unshift($ids, $this->cacheTag);
        }

        return $ids;
    }
}
