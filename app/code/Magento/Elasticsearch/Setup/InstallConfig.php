<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Setup;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Setup\Model\SearchConfigOptionsList;
use Magento\Search\Setup\InstallConfigInterface;

/**
 * Configure Elasticsearch search engine based on installation input
 */
class InstallConfig implements InstallConfigInterface
{
    private const CATALOG_SEARCH = 'catalog/search/';

    /**
     * @var array
     */
    private $searchConfigMapping = [
        SearchConfigOptionsList::INPUT_KEY_SEARCH_ENGINE => 'engine'
    ];

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @param WriterInterface $configWriter
     * @param array $searchConfigMapping
     */
    public function __construct(
        WriterInterface $configWriter,
        array $searchConfigMapping = []
    ) {
        $this->configWriter = $configWriter;
        $this->searchConfigMapping = array_merge($this->searchConfigMapping, $searchConfigMapping);
    }

    /**
     * @inheritDoc
     */
    public function configure(array $inputOptions)
    {
        foreach ($inputOptions as $inputKey => $inputValue) {
            if (null === $inputValue || !isset($this->searchConfigMapping[$inputKey])) {
                continue;
            }
            $configKey = $this->searchConfigMapping[$inputKey];
            $this->configWriter->save(self::CATALOG_SEARCH . $configKey, $inputValue);
        }
    }
}
