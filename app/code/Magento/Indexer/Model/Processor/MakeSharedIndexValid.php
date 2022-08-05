<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Indexer\Model\Processor;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer\State;

/**
 * Class processor makes indexers valid by shared index ID
 */
class MakeSharedIndexValid
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * ValidateSharedIndex constructor.
     *
     * @param ConfigInterface $config
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(ConfigInterface $config, IndexerRegistry $indexerRegistry)
    {
        $this->config = $config;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Validate indexers by shared index ID
     *
     * @param string $sharedIndex
     * @return bool
     * @throws \Exception
     */
    public function execute(string $sharedIndex): bool
    {
        if (empty($sharedIndex)) {
            throw new \InvalidArgumentException(
                "The '{$sharedIndex}' is an invalid shared index identifier. Verify the identifier and try again.",
            );
        }

        $indexerIds = $this->getIndexerIdsBySharedIndex($sharedIndex);
        if (empty($indexerIds)) {
            return false;
        }

        foreach ($indexerIds as $indexerId) {
            $indexer = $this->indexerRegistry->get($indexerId);
            /** @var State $state */
            $state = $indexer->getState();
            $state->setStatus(StateInterface::STATUS_WORKING);
            $state->save();
            $state->setStatus(StateInterface::STATUS_VALID);
            $state->save();
        }

        return true;
    }

    /**
     * Get indexer ids that have common shared index
     *
     * @param string $sharedIndex
     * @return array
     */
    private function getIndexerIdsBySharedIndex(string $sharedIndex): array
    {
        $indexers = $this->config->getIndexers();

        $result = [];
        foreach ($indexers as $indexerConfig) {
            if ($indexerConfig['shared_index'] == $sharedIndex) {
                $result[] = $indexerConfig['indexer_id'];
            }
        }

        return $result;
    }
}
