<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Filter;

/**
 * Checkbox grid column filter
 */
class Radio extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Return array of options
     *
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
     * @inheritDoc
     */
    public function getCondition()
    {
        if ($this->getValue()) {
            return $this->getColumn()->getValue();
        }
        return [['neq' => $this->getColumn()->getValue()], ['is' => new \Zend_Db_Expr('NULL')]];
    }
}
