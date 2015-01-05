<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Massaction grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SkipList extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        return ['nin' => $this->getValue() ?: [0]];
    }
}
