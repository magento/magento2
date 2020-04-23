<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\Setup\Option\AbstractConfigOption;
use Magento\Framework\Validation\ValidationException;
use Magento\Search\Model\SearchEngine\Validator as SearchEngineValidator;
use Magento\Search\Setup\CompositeInstallConfig as InstallConfig;
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
     * @var InstallConfig
     */
    private $installConfig;

    /**
     * @var SearchEngineValidator
     */
    private $searchValidator;

    /**
     * @param SearchConfigOptionsList $searchConfigOptionsList
     * @param InstallConfig $installConfig
     * @param SearchEngineValidator $searchValidator
     */
    public function __construct(
        SearchConfigOptionsList $searchConfigOptionsList,
        InstallConfig $installConfig,
        SearchEngineValidator $searchValidator
    ) {
        $this->searchConfigOptionsList = $searchConfigOptionsList;
        $this->installConfig = $installConfig;
        $this->searchValidator = $searchValidator;
    }

    /**
     * Save the search engine configuration
     *
     * @param array $inputOptions
     * @throws SetupException
     * @throws ValidationException
     */
    public function saveConfiguration(array $inputOptions)
    {
        $searchConfigOptions = $this->extractSearchOptions($inputOptions);
        if (!empty($searchConfigOptions[SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE])) {
            $this->validateSearchEngineSelection($searchConfigOptions);
        }
        try {
            $this->installConfig->configure($searchConfigOptions);
        } catch (\Exception $e) {
            throw new SetupException($e->getMessage());
        }
        $this->validateSearchEngine($searchConfigOptions);
    }

    /**
     * Validate search engine
     *
     * @param array $config
     * @throws ValidationException
     */
    public function validateSearchEngine(array $config = [])
    {
        $validationErrors = $this->searchValidator->validate($config);
        if (!empty($validationErrors)) {
            throw new ValidationException(__(implode(PHP_EOL, $validationErrors)));
        }
    }

    /**
     * Validate the selected search engine
     *
     * @param array $searchOptions
     * @throws SetupException
     */
    private function validateSearchEngineSelection(array $searchOptions)
    {
        if (isset($searchOptions[SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE])) {
            $selectedEngine = $searchOptions[SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE];
            $availableEngines = $this->searchConfigOptionsList->getAvailableSearchEngineList();
            if (!isset($availableEngines[$selectedEngine])) {
                throw new SetupException("Search engine '{$selectedEngine}' is not an available search engine.");
            }
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
            $optionName = $option->getName();
            if (isset($inputOptions[$optionName])) {
                $searchOptions[$optionName] = $inputOptions[$optionName];
            }
        }
        return $searchOptions;
    }
}
