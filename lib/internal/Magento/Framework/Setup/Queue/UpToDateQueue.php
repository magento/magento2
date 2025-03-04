<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Queue;

use Magento\Framework\Setup\UpToDateValidatorInterface;
use Magento\Framework\Module\ModuleList;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Component\ComponentRegistrar;

class UpToDateQueue implements UpToDateValidatorInterface
{
    /**
     * @param ModuleList $moduleList
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param File $fileDriver
     * @param Parser $xmlParser
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        private readonly ModuleList $moduleList,
        private readonly LoggerInterface $logger,
        private readonly ResourceConnection $resourceConnection,
        private readonly File $fileDriver,
        private readonly Parser $xmlParser,
        private readonly ComponentRegistrar $componentRegistrar
    ) {}

    /**
     * @return string
     */
    public function getNotUpToDateMessage(): string
    {
        return 'Queue is not up to date';
    }

    /**
     * @return bool
     */
    public function isUpToDate(): bool
    {
        $existingQueues = $this->getQueueFromDatabase();
        $queueFromXml = $this->getQueueFromXml();

        return $this->queuesMatch($existingQueues, $queueFromXml);
    }

    private function queuesMatch(array $existingQueues, array $queueFromXml): bool
    {
        if (count($existingQueues) !== count($queueFromXml)) {
            return false;
        }

        sort($existingQueues);
        sort($queueFromXml);

        return $existingQueues === $queueFromXml;
    }

    private function getQueueFromDatabase(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('queue');
        $select = $connection->select()->distinct()->from($tableName, ['name']);
        return $connection->fetchCol($select);
    }

    private function getQueueFromXml(): array
    {
        $queues = [];

        foreach ($this->moduleList->getAll() as $moduleName) {
            $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName['name']);

            if (!$modulePath) {
                continue;
            }

            $queueFile = $modulePath . '/etc/queue_consumer.xml';

            if (!$this->fileDriver->isExists($queueFile)) {
                continue;
            }

            try {
                $xmlContent = $this->fileDriver->fileGetContents($queueFile);
                $parsedXml = $this->xmlParser->loadXML($xmlContent)->xmlToArray();

                if (isset($parsedXml['config']['_value']['consumer'])) {
                    $consumers = $parsedXml['config']['_value']['consumer'];

                    foreach ($consumers as $item) {
                        if (isset($item['_attribute']['queue'])) {
                            $queues[] = $item['_attribute']['queue'];
                        } else {
                            if (isset($item['queue'])) {
                                $queues[] = $item['queue'];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                continue;
            }
        }
        return array_unique($queues);
    }
}
