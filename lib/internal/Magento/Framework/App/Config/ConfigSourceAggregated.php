<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class ConfigSourceAggregated implements ConfigSourceInterface
{
    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;

    /**
     * ConfigSourceAggregated constructor.
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
    }

    /**
     * Retrieve aggregated configuration from all available sources.
     *
     * @param string $path
     * @return string|array
     */
    public function get($path = '')
    {
        $this->sortSources();
        $data = [];
        foreach ($this->sources as $sourceConfig) {
            /** @var ConfigSourceInterface $source */
            $source = $sourceConfig['source'];
            $configData = $source->get($path);
            if (!is_array($configData)) {
                $data = $configData;
            } elseif (!empty($configData)) {
                $data = array_replace_recursive(is_array($data) ? $data : [], $configData);
            }
        }
        return $data;
    }

    /**
     * Sort sources
     *
     * @return void
     */
    private function sortSources()
    {
        uasort($this->sources, function ($firstItem, $secondItem) {
            return $firstItem['sortOrder'] > $secondItem['sortOrder'];
        });
    }
}
