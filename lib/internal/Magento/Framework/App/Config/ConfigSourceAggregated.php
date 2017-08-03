<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Class \Magento\Framework\App\Config\ConfigSourceAggregated
 *
 * @since 2.2.0
 */
class ConfigSourceAggregated implements ConfigSourceInterface
{
    /**
     * @var ConfigSourceInterface[]
     * @since 2.2.0
     */
    private $sources;

    /**
     * ConfigSourceAggregated constructor.
     *
     * @param array $sources
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function sortSources()
    {
        uasort($this->sources, function ($firstItem, $secondItem) {
            return $firstItem['sortOrder'] > $secondItem['sortOrder'];
        });
    }
}
