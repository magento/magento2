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
     * Rule name for include configuration data.
     */
    const RULE_TYPE_INCLUDE = 'include';

    /**
     * Rule name for exclude configuration data.
     */
    const RULE_TYPE_EXCLUDE = 'exclude';

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
     * Array of rules for filtration the configuration data.
     *
     * For example:
     * ```php
     * [
     *     'default' => 'include',
     *     'sensitive' => 'exclude',
     *     'environment' => 'exclude',
     * ]
     * ```
     * It means that all aggregated configuration data will be included in result but configurations
     * that relates to 'sensitive' or 'environment' will be excluded.
     *
     *
     * ```php
     * [
     *     'default' => 'exclude',
     *     'sensitive' => 'include',
     *     'environment' => 'include',
     * ]
     * ```
     * It means that result will contains only 'sensitive' and 'environment' configurations.
     *
     * @var array
     */
    private $rules;

    /**
     * @param ExcludeList $excludeList Is not used anymore as it was deprecated, use TypePool instead.
     * @param array $sources
     * @param TypePool|null $typePool
     * @param array $rules Rules for filtration the configuration data.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExcludeList $excludeList,
        array $sources = [],
        TypePool $typePool = null,
        array $rules = []
    ) {
        $this->sources = $sources;
        $this->typePool = $typePool ?: ObjectManager::getInstance()->get(TypePool::class);
        $this->rules = $rules;
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

            if (is_array($subData)) {
                $this->filterChain($newPath, $subData);
            } elseif ($this->isExcludedPath($filteredPath)) {
                $this->excludedFields[$newPath] = $filteredPath;
                unset($data[$subKey]);
            }

            if (empty($subData) && isset($data[$subKey]) && is_array($data[$subKey])) {
                unset($data[$subKey]);
            }
        }
    }

    /**
     * Checks if the configuration field needs to be excluded.
     *
     * @param string $path Configuration field path. For example 'contact/email/recipient_email'
     * @return boolean Return true if path should be excluded
     */
    private function isExcludedPath($path)
    {
        if (empty($path)) {
            return false;
        }

        $defaultRule = isset($this->rules['default']) ?
            $this->rules['default'] : self::RULE_TYPE_INCLUDE;

        foreach ($this->rules as $type => $rule) {
            if ($type === 'default') {
                continue;
            }

            if ($this->typePool->isPresent($path, $type)) {
                return $rule === self::RULE_TYPE_EXCLUDE;
            }
        }

        return $defaultRule === self::RULE_TYPE_EXCLUDE;
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
