<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\Filter\FilterAbstract;

/**
 * Class Range
 */
class Range extends FilterAbstract
{
    /**
     * Get condition by data type
     *
     * @param array|string $value
     * @return array|null
     */
    public function getCondition($value)
    {
        if (!empty($value['from']) || !empty($value['to'])) {
            if (isset($value['from']) && empty($value['from']) && $value['from'] !== '0') {
                $value['orig_from'] = $value['from'];
                $value['from'] = null;
            }
            if (isset($value['to']) && empty($value['to']) && $value['to'] !== '0') {
                $value['orig_to'] = $value['to'];
                $value['to'] = null;
            }
        } else {
            $value = null;
        }

        return $value;
    }
}
