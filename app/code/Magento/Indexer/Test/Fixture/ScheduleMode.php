<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class ScheduleMode implements RevertibleDataFixtureInterface
{
    /**
     * @var IndexerRegistry
     */
    private IndexerRegistry $indexerRegistry;

    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'indexer' => (string) Indexer code. Required.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->indexerRegistry->get($data['indexer'])->setScheduled(true);

        return $this->dataObjectFactory->create(['data' => $data]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->indexerRegistry->get($data['indexer'])->setScheduled(false);
    }
}
