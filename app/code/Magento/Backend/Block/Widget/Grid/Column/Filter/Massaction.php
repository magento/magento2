<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Massaction grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Checkbox
{
    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        if ($this->getValue()) {
            return ['in' => $this->getColumn()->getSelected() ? $this->getColumn()->getSelected() : [0]];
        } else {
            return ['nin' => $this->getColumn()->getSelected() ? $this->getColumn()->getSelected() : [0]];
        }
    }
}
