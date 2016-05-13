<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Block\Cache;

use Magento\Backend\Test\Block\Widget\Grid as ParentGrid;

/**
 * Backend Cache Management grid.
 */
class Grid extends ParentGrid
{
    /**
     * Search for item and select it.
     *
     * @param array $filter
     * @return void
     * @throws \Exception
     */
    public function searchAndSelect(array $filter)
    {
        $selectItem = $this->getRow($filter, false)->find($this->selectItem);
        if ($selectItem->isVisible()) {
            $selectItem->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }
}
