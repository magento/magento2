<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\GraphQl\Query\IdentityResolverInterface;

/**
 * Identity for resolved CMS block
 */
class IdentityResolver implements IdentityResolverInterface
{
    /**
     * Get block identifiers from resolved data
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            if (is_array($item) && !empty($item[BlockInterface::IDENTIFIER])) {
                $ids[] = $item[BlockInterface::IDENTIFIER ];
            }
        }

        return $ids;
    }
}
