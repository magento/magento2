<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

/**
 * Applies filters to a directive value
 */
class FilterApplier
{
    /**
     * @var FilterPool
     */
    private $filterPool;

    /**
     * @param FilterPool $filterPool
     */
    public function __construct(FilterPool $filterPool)
    {
        $this->filterPool = $filterPool;
    }

    /**
     * Apply the filters based on the raw directive value
     *
     * For example: applyFromRawParam('|escape:html|nl2br', 'a value', ['escape']);
     *
     * @param string $param The raw directive filters
     * @param string $value The input to filter
     * @param string[] $defaultFilters The default filters that should be applied if none are parsed
     * @return string The filtered string
     */
    public function applyFromRawParam(string $param, string $value, array $defaultFilters = []): string
    {
        $filters = array_filter(explode('|', ltrim($param, '|')));

        if (empty($filters)) {
            $filters = $defaultFilters;
        }

        return $this->applyFromArray($filters, $value);
    }

    /**
     * Apply a given list of named filters
     *
     * For example: applyFromArray(['escape:html','nl2br], 'a value');
     *
     * @param string[] $filters The list of filter names to apply
     * @param string $value The input to filter
     * @return string The filtered string
     */
    public function applyFromArray(array $filters, string $value): string
    {
        $filters = array_filter($filters);

        foreach ($filters as $filter) {
            $params = explode(':', $filter);
            $filterName = array_shift($params);
            try {
                $filter = $this->filterPool->get($filterName);
            } catch (\InvalidArgumentException $e) {
                continue;
            }

            $value = $filter->filterValue($value, $params);
        }

        return $value;
    }
}
