<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use InvalidArgumentException;
use Laminas\Filter\FilterChain;
use Laminas\Filter\FilterInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

class DataObject
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
     * @var EntityFactoryInterface
     */
    protected $_entityFactory;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param FilterChain|null $filterChain
     */
    public function __construct(EntityFactoryInterface $entityFactory, FilterChain $filterChain = null)
    {
        $this->filterChain = $filterChain ?? new FilterChain();
        $this->_entityFactory = $entityFactory;
    }

    /**
     * Method to add filter.
     *
     * @param FilterInterface $filter
     * @param string $column
     *
     * @return DataObject
     */
    public function addFilter(FilterInterface $filter, $column = '')
    {
        if ('' === $column) {
            $this->filterChain->setOptions(['callbacks' => [['callback' => $filter]]]);
        } else {
            if (!isset($this->_columnFilters[$column])) {
                $this->_columnFilters[$column] = new FilterChain();
            }
            $this->_columnFilters[$column]->setOptions(['callbacks' => [['callback' => $filter]]]);
        }

        return $this;
    }

    /**
     * Method filter.
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    public function filter($object)
    {
        if (!$object instanceof \Magento\Framework\DataObject) {
            throw new InvalidArgumentException('Expecting an instance of \Magento\Framework\DataObject');
        }
        $class = get_class($object);
        $out = $this->_entityFactory->create($class);
        foreach ($object->getData() as $column => $value) {
            $value = $this->filterChain->filter($value);
            if (isset($this->_columnFilters[$column])) {
                $value = $this->_columnFilters[$column]->filter($value);
            }
            $out->setData($column, $value);
        }
        return $out;
    }
}
