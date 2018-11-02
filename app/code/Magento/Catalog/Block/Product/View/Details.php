<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

/**
 * Product details block
 * Holds a group of blocks to show as tabs
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Catalog\Block\Product\View;

/**
 * @api
 */
class Details extends \Magento\Framework\View\Element\Template
{
    /**
     * @param string $groupName
     * @param $callback
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function getGroupSortedChildNames(string $groupName, $callback): array
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
