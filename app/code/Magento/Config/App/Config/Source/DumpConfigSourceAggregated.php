<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Config\Model\Config\TypePool;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class DumpConfigSourceAggregated aggregates configurations from all available sources
 */
class DumpConfigSourceAggregated implements DumpConfigSourceInterface
{
    /**
     * @var ExcludeList
     */
    private $excludeList;

    /**
     * Checker for config type.
     *
     * @var TypePool
     */
    private $typePool;

    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;

    /**
     * @var array
     */
    private $excludedFields;

    /**
     * @var array
     */
    private $data;

    /**
     * @param ExcludeList $excludeList
     * @param array $sources
     * @param TypePool|null $typePool
     */
    public function __construct(ExcludeList $excludeList, array $sources = [], TypePool $typePool = null)
    {
        $this->excludeList = $excludeList;
        $this->sources = $sources;
        $this->typePool = $typePool ?: ObjectManager::getInstance()->get(TypePool::class);
    }

    /**
     * Retrieve aggregated configuration from all available sources.
     *
     * @param string $path
     * @return array
     */
    public function get($path = '')
    {
        $path = (string)$path;
        $data = [];

        if (isset($this->data[$path])) {
            return $this->data[$path];
        }

        $this->sortSources();

        foreach ($this->sources as $sourceConfig) {
            /** @var ConfigSourceInterface $source */
            $source = $sourceConfig['source'];
            $data = array_replace_recursive($data, $source->get($path));
        }

        $this->excludedFields = [];
        $this->filterChain($path, $data);

        return $this->data[$path] = $data;
    }

    /**
     * Recursive filtering of sensitive data
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    private function filterChain($path, &$data)
    {
        foreach ($data as $subKey => &$subData) {
            $newPath = $path ? $path . '/' . $subKey : $subKey;
            $filteredPath = $this->filterPath($newPath);

            if ($filteredPath
                && !is_array($data[$subKey])
                && $this->isExcludePath($filteredPath)
            ) {
                $this->excludedFields[$newPath] = $filteredPath;

                unset($data[$subKey]);
            } elseif (is_array($subData)) {
                $this->filterChain($newPath, $subData);
            }
        }
    }

    /**
     * Checks if the configuration field belongs to a sensitive type.
     *
     * @param string $path Configuration field path. For example 'contact/email/recipient_email'
     * @return boolean
     */
    private function isExcludePath($path)
    {
        return $this->excludeList->isPresent($path)
            || $this->typePool->isPresent($path, TypePool::TYPE_ENVIRONMENT)
            || $this->typePool->isPresent($path, TypePool::TYPE_SENSITIVE);
    }

    /**
     * Eliminating scope info from path
     *
     * @param string $path
     * @return null|string
     */
    private function filterPath($path)
    {
        $parts = explode('/', $path);

        // Check if there are enough parts to recognize scope
        if (count($parts) < 3) {
            return null;
        }

        if ($parts[0] === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            unset($parts[0]);
        } else {
            unset($parts[0], $parts[1]);
        }

        return implode('/', $parts);
    }

    /**
     * Sort sources ASC from higher priority to lower
     *
     * @return void
     */
    private function sortSources()
    {
        uasort($this->sources, function ($firstItem, $secondItem) {
            return $firstItem['sortOrder'] > $secondItem['sortOrder'];
        });
    }

    /**
     * Retrieves list of field paths were excluded from config dump
     * @return array
     */
    public function getExcludedFields()
    {
        $this->get();

        $fields = array_values($this->excludedFields);
        $fields = array_unique($fields);

        return $fields;
    }
}
