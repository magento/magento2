<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;

class ArrayFilter
{
    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * @var array
     */
    protected $_columnFilters = [];

    /**
     * @param FilterChain|null $filterChain
     */
    public function __construct(?FilterChain $filterChain = null)
    {
        $this->filterChain = $filterChain ?? new FilterChain();
    }

    /**
     * Method to add filer.
     *
     * @param FilterInterface $filter
     * @param string $column
     * @return ArrayFilter
     */
    public function addFilter(FilterInterface $filter, $column = '')
    {
        if ($column !== '') {
            $this->_columnFilters[$column] = $this->_columnFilters[$column] ?? new FilterChain();
            $this->_columnFilters[$column]->setOptions(['callbacks' => [['callback' => $filter]]]);
        } else {
            $this->filterChain->setOptions(['callbacks' => [['callback' => $filter]]]);
        }

        return $this;
    }

    /**
     * Returns $value filtered through each filter in the chain.
     *
     * @param array $array
     * @return array
     */
    public function filter($array)
    {
        $out = [];

        foreach ($array as $column => $value) {
            $value = $this->filterChain->filter($value);
            if (isset($this->_columnFilters[$column])) {
                $value = $this->_columnFilters[$column]->filter($value);
            }
            $out[$column] = $value;
        }

        return $out;
    }
}
