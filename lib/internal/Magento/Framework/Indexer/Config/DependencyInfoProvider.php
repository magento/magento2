<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Phrase;

/**
 * @inheritdoc
 */
class DependencyInfoProvider implements DependencyInfoProviderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getIndexerIdsToRunBefore(string $indexerId)
    {
        return $this->getIndexerDataWithValidation($indexerId)['dependencies'];
    }

    /**
     * @inheritdoc
     */
    public function getIndexerIdsToRunAfter(string  $indexerId)
    {
        /** check indexer existence */
        $this->getIndexerDataWithValidation($indexerId);
        $result = [];
        foreach ($this->config->getIndexers() as $id => $indexerData) {
            if (array_search($indexerId, $indexerData['dependencies']) !== false) {
                $result[] = $id;
            }
        };

        return $result;
    }

    /**
     * Return the indexer data from the configuration.
     *
     * @param string $indexerId
     * @return array
     */
    private function getIndexerData(string $indexerId)
    {
        return $this->config->getIndexer($indexerId);
    }

    /**
     * Return the indexer data from the configuration and validate this data.
     *
     * @param string $indexerId
     * @return array
     * @throws NoSuchEntityException In case when the indexer with the specified Id does not exist.
     */
    private function getIndexerDataWithValidation(string $indexerId)
    {
        $indexerData = $this->getIndexerData($indexerId);
        if (empty($indexerData) || empty($indexerData['indexer_id']) || $indexerData['indexer_id'] != $indexerId) {
            throw new NoSuchEntityException(
                new Phrase("{$indexerId} indexer does not exist.")
            );
        }

        return $indexerData;
    }
}
