<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Checkbox grid column filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @return array
     */
    protected function _getOptions()
    {
        return [
            ['label' => __('Any'), 'value' => ''],
            ['label' => __('Yes'), 'value' => 1],
            ['label' => __('No'), 'value' => 0]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCondition()
    {
        if ($this->getValue()) {
            return $this->getColumn()->getValue();
        } else {
            return [['neq' => $this->getColumn()->getValue()], ['is' => new \Zend_Db_Expr('NULL')]];
        }
    }
}
