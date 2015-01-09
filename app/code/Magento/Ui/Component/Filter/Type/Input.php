<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\Filter\FilterAbstract;

/**
 * Class Input
 */
class Input extends FilterAbstract
{
    /**
     * Get condition by data type
     *
     * @param string|array $value
     * @return array|null
     */
    public function getCondition($value)
    {
        $condition = null;
        if (!empty($value) || is_numeric($value)) {
            $condition = ['like' => sprintf('%%%s%%', $value)];
        }

        return $condition;
    }
}
