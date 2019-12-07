<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class ArrayFilter extends \Zend_Filter
{
    /**
     * @var array
     */
    protected $_columnFilters = [];

    /**
     * {@inheritdoc}
     *
     * @param \Zend_Filter_Interface $filter
     * @param string $column
     * @return null|\Zend_Filter
     */
    public function addFilter(\Zend_Filter_Interface $filter, $column = '')
    {
        if ('' === $column) {
            parent::addFilter($filter);
        } else {
            if (!isset($this->_columnFilters[$column])) {
                $this->_columnFilters[$column] = new \Zend_Filter();
            }
            $this->_columnFilters[$column]->addFilter($filter);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param array $array
     * @return array
     */
    public function filter($array)
    {
        $out = [];
        foreach ($array as $column => $value) {
            $value = parent::filter($value);
            if (isset($this->_columnFilters[$column])) {
                $value = $this->_columnFilters[$column]->filter($value);
            }
            $out[$column] = $value;
        }
        return $out;
    }
}
