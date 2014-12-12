<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filter\Object;

use Magento\Framework\Object;

class Grid extends \Magento\Framework\Filter\Object
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
