<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

use Magento\Framework\Filter\DirectiveProcessor\FilterInterface;

/**
 * Container for directive output filters
 */
class FilterPool
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof FilterInterface) {
                throw new \InvalidArgumentException('Directive filters must implement ' . FilterInterface::class);
            }
        }

        $this->filters = $filters;
    }

    /**
     * Return a filter from the pool
     *
     * @param string $name
     * @return FilterInterface
     */
    public function get(string $name): FilterInterface
    {
        if (empty($this->filters[$name])) {
            throw new \InvalidArgumentException('Filter with key "' . $name . '" has not been defined');
        }

        return $this->filters[$name];
    }
}
