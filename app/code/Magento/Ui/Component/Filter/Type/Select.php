<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\Filter\FilterAbstract;

/**
 * Class Select
 */
class Select extends FilterAbstract
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
            $condition = ['eq' => $value];
        }

        return $condition;
    }
}
