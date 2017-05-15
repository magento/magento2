<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Sequence;

use Psr\Log\LoggerInterface;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class SequenceManager
 */
class SequenceManager
{
    /**
     * @var SequenceRegistry
     */
    private $sequenceRegistry;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $appResource;

    /**
     * @param MetadataPool $metadataPool
     * @param SequenceRegistry $sequenceRegistry
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $appResource
     */
    public function __construct(
        MetadataPool $metadataPool,
        SequenceRegistry $sequenceRegistry,
        LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $appResource
    ) {
        $this->metadataPool = $metadataPool;
        $this->sequenceRegistry = $sequenceRegistry;
        $this->logger = $logger;
        $this->appResource = $appResource;
    }

    /**
     * Force sequence value creation
     *
     * @param string $entityType
     * @param string|int $identifier
     * @return int
     * @throws \Exception
     */
    public function force($entityType, $identifier)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $sequenceInfo = $this->sequenceRegistry->retrieve($entityType);

        if (!isset($sequenceInfo['sequenceTable'])) {
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL  . ' Sequence table doesnt exists');
        }
        try {
            $connection = $this->appResource->getConnectionByName($metadata->getEntityConnectionName());
            return $connection->insert(
                $this->appResource->getTableName($sequenceInfo['sequenceTable']),
                ['sequence_value' => $identifier]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . $e->getMessage());
        }
    }

    /**
     * @param string $entityType
     * @param int $identifier
     * @return int
     * @throws \Exception
     */
    public function delete($entityType, $identifier)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $sequenceInfo = $this->sequenceRegistry->retrieve($entityType);
        if (!isset($sequenceInfo['sequenceTable'])) {
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL  . ' Sequence table doesnt exists');
        }
        try {
            $connection = $this->appResource->getConnectionByName($metadata->getEntityConnectionName());
            return $connection->delete(
                $this->appResource->getTableName($sequenceInfo['sequenceTable']),
                ['sequence_value = ?' => $identifier]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new \Exception('TODO: use correct Exception class' . PHP_EOL . $e->getMessage());
        }
    }
}
