<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Class \Magento\Framework\Filter\ArrayFilter
 *
 * @since 2.0.0
 */
class ArrayFilter extends \Zend_Filter
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_columnFilters = [];

    /**
     * {@inheritdoc}
     *
     * @param \Zend_Filter_Interface $filter
     * @param string $column
     * @return null|\Zend_Filter
     * @since 2.0.0
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
     * @since 2.0.0
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
