<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\Setup\Option\AbstractConfigOption;
use Magento\Search\Setup\InstallConfigInterface;
use Magento\Setup\Exception as SetupException;

/**
 * Configure search engine
 */
class SearchConfig
{
    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    /**
     * @var InstallConfigInterface
     */
    private $installConfig;

    /**
     * @param SearchConfigOptionsList $searchConfigOptionsList
     * @param InstallConfigInterface $installConfig
     */
    public function __construct(
        SearchConfigOptionsList $searchConfigOptionsList,
        InstallConfigInterface $installConfig
    ) {
        $this->searchConfigOptionsList = $searchConfigOptionsList;
        $this->installConfig = $installConfig;
    }

    /**
     * Save the search engine configuration
     *
     * @param array $inputOptions
     * @throws SetupException
     */
    public function saveConfiguration(array $inputOptions)
    {
        $searchConfigOptions = $this->extractSearchOptions($inputOptions);
        $this->validateSearchEngineSelection($searchConfigOptions);

        $this->installConfig->configure($searchConfigOptions);
    }

    /**
     * Validate the selected search engine
     *
     * @param array $searchOptions
     * @throws SetupException
     */
    private function validateSearchEngineSelection(array $searchOptions)
    {
        $selectedEngine = $searchOptions[SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE];
        if (!in_array($selectedEngine, SearchConfigOptionsList::AVAILABLE_SEARCH_ENGINES)) {
            throw new SetupException("Search engine '{$selectedEngine}' is not an available search engine.");
        }
    }

    /**
     * Extract configuration options for search
     *
     * @param array $inputOptions
     * @return array
     */
    private function extractSearchOptions(array $inputOptions): array
    {
        $searchOptions = [];
        $installOptions = $this->searchConfigOptionsList->getOptionsList();
        /** @var AbstractConfigOption $option */
        foreach ($installOptions as $option) {
            $installOption = $option->getName();
            if (isset($inputOptions[$installOption])) {
                $searchOptions[$installOption] = $inputOptions[$installOption];
            }
        }
        return $searchOptions;
    }
}
