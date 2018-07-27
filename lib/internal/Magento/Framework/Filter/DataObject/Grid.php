<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\DataObject;

use Magento\Framework\DataObject;

class Grid extends \Magento\Framework\Filter\DataObject
{
    /**
     * @param Object[] $grid
     * @return Object[]
     */
    public function filter($grid)
    {
        $out = [];
        if (is_array($grid)) {
            foreach ($grid as $key => $gridItem) {
                $out[$key] = parent::filter($gridItem);
            }
        }
        return $out;
    }
}
