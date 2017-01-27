<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigShow;

use Magento\Framework\App\Config\ConfigSourceInterface;

/**
 * Configuration object used for ConfigShowCommand
 */
class ConfigSourceAggregated implements ConfigSourceInterface
{
    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;

    /**
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
     * @return array
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
                return $configData;
            }
            $data = array_replace_recursive($data, $configData);
        }

        return $data;
    }

    /**
     * Sort given sources
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
