<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

/**
 * Compare object state between requests
 */
class Comparator
{
    /**
     * @var Collector
     */
    private Collector $collector;

    /** @var array */
    private array $objectsStateBefore = [];

    /**
     * @var array
     */
    private array $objectsStateAfter = [];

    /**
     * @var array|null
     */
    private ?array $skipList = null;

    /**
     * @var array|null
     */
    private ?array $filterList = null;

    /**
     * @param Collector $collector
     */
    public function __construct(Collector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * Remember shared object state before request
     *
     * @param bool $firstRequest
     * @throws \Exception
     */
    public function rememberObjectsStateBefore(bool $firstRequest): void
    {
        if ($firstRequest) {
            $this->objectsStateBefore = $this->collector->getSharedObjects();
        }
    }

    /**
     * Remember shared object state after request
     *
     * @param bool $firstRequest
     * @throws \Exception
     */
    public function rememberObjectsStateAfter(bool $firstRequest): void
    {
        $this->objectsStateAfter = $this->collector->getSharedObjects();
        if ($firstRequest) {
            // on the end of first request add objects to init object state pool
            $this->objectsStateBefore = array_merge($this->objectsStateAfter, $this->objectsStateBefore);
        }
    }

    /**
     * Compare objectsStateAfter with objectsStateBefore
     *
     * @param string $operationName
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function compare(string $operationName): array
    {
        $compareResults = [];
        $skipList = $this->getSkipList($operationName);
        $filterList = $this->getFilterList();
        $filterListParentClasses = $filterList['parents'] ?? [];
        $filterListServices = $filterList['services'] ?? [];
        $filterListAll = $filterList['all'] ?? [];
        foreach ($this->objectsStateAfter as $serviceName => $service) {
            [$object, $properties] = $service;
            if (array_key_exists($serviceName, $skipList)) {
                continue;
            }
            $objectState = [];
            if (!isset($this->objectsStateBefore[$serviceName])) {
                $compareResults[$serviceName] = 'new object appeared after first request';
            } else {
                $propertiesToFilterList = [];
                if (isset($filterListServices[$serviceName])) {
                    $propertiesToFilterList[] = $filterListServices[$serviceName];
                }
                foreach ($filterListParentClasses as $parentClass => $excludeProperties) {
                    if ($object instanceof $parentClass) {
                        $propertiesToFilterList[] = $excludeProperties;
                    }
                }
                if ($filterListAll) {
                    $propertiesToFilterList[] = $filterListAll;
                }
                $properties = $this->filterProperties($properties, $propertiesToFilterList);
                [$beforeObject, $beforeProperties] = $this->objectsStateBefore[$serviceName];
                if ($beforeObject !== $object) {
                    $compareResults[$serviceName] = 'has new instance of object';
                }
                foreach ($properties as $propertyName => $propertyValue) {
                    $result = $this->checkValues($beforeProperties[$propertyName] ?? null, $propertyValue);
                    if ($result) {
                        $objectState[$propertyName] = $result;
                    }
                }
            }
            if ($objectState) {
                $compareResults[$serviceName] = $objectState;
            }
        }
        return $compareResults;
    }

    /**
     * Filters properties by the list of property filters
     *
     * @param array $properties
     * @param array $propertiesToFilterList
     * @return array
     */
    private function filterProperties($properties, $propertiesToFilterList): array
    {
        return array_diff_key($properties, ...$propertiesToFilterList);
    }

    /**
     * Gets skipList, loading it if needed
     *
     * @param string $operationName
     * @return array
     */
    private function getSkipList($operationName): array
    {
        if ($this->skipList === null) {
            $skipListList = [];
            foreach (glob(__DIR__ . '/../../_files/state-skip-list*.php') as $skipListFile) {
                $skipListList[] = include($skipListFile);
            }
            $this->skipList = array_merge_recursive(...$skipListList);
        }
        return array_merge($this->skipList['*'], $this->skipList[$operationName] ?? []);
    }

    /**
     * Gets filterList, loading it if needed
     *
     * @return array
     */
    private function getFilterList(): array
    {
        if ($this->filterList === null) {
            $filterListList = [];
            foreach (glob(__DIR__ . '/../../_files/state-filter-list*.php') as $filterListFile) {
                $filterListList[] = include($filterListFile);
            }
            $this->filterList = array_merge_recursive(...$filterListList);
        }
        return $this->filterList;
    }

    /**
     * Formats value by type
     *
     * @param mixed $type
     * @return array
     */
    private function formatValue($type): array
    {
        $type = is_array($type) ? $type : [$type];
        $data = [];
        foreach ($type as $k => $v) {
            if (is_object($v)) {
                $v = get_class($v);
            } elseif (is_array($v)) {
                $v = $this->formatValue($v);
            }
            $data[$k] = $v;
        }
        return $data;
    }

    /**
     * Compares the values, returns the differences.
     *
     * @param mixed $before
     * @param mixed $after
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function checkValues($before, $after): array
    {
        $result = [];
        $typeBefore = gettype($before);
        $typeAfter = gettype($after);
        if ($typeBefore !== $typeAfter) {
            $result['before'] = $this->formatValue($before);
            $result['after'] = $this->formatValue($after);
            return $result;
        }
        switch ($typeBefore) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                if ($before !== $after) {
                    $result['before'] = $before;
                    $result['after'] = $after;
                }
                break;
            case 'array':
                if (count($before) !== count($after) || $before != $after) {
                    $result['before'] = $this->formatValue($before);
                    $result['after'] = $this->formatValue($after);
                }
                break;
            case 'object':
                if ($before != $after) {
                    $result['before'] = get_class($before);
                    $result['after'] = get_class($after);
                }
                break;
        }
        return $result;
    }
}
