<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

/**
 * Product details block.
 *
 * Holds a group of blocks to show as tabs.
 *
 * @api
 */
class Details extends \Magento\Framework\View\Element\Template
{
    /**
     * Get sorted child block names.
     *
     * @param string $groupName
     * @param string $callback
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function getGroupSortedChildNames(string $groupName, string $callback): array
    {
        $groupChildNames = $this->getGroupChildNames($groupName, $callback);
        $layout = $this->getLayout();

        $childNamesSortOrder = [];

        foreach ($groupChildNames as $childName) {
            $alias = $layout->getElementAlias($childName);
            $sortOrder = (int)$this->getChildData($alias, 'sort_order') ?? 0;

            $childNamesSortOrder[$sortOrder] = $childName;
        }

        ksort($childNamesSortOrder, SORT_NUMERIC);

        return $childNamesSortOrder;
    }
}
