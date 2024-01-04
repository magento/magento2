<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\DataFixtureInterface;
use Magento\Indexer\Model\Indexer as IndexerModel;
use Magento\Indexer\Model\Indexer\Collection;

class Indexer implements DataFixtureInterface
{
    /**
     * @var Collection
     */
    private Collection $indexerCollection;

    /**
     * @param Collection $indexerCollection
     */
    public function __construct(
        Collection $indexerCollection
    ) {
        $this->indexerCollection = $indexerCollection;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->indexerCollection->load();
        /** @var IndexerModel $indexer */
        foreach ($this->indexerCollection->getItems() as $indexer) {
            $indexer->reindexAll();
        }
        return null;
    }
}
