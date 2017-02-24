<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
