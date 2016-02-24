<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model;

use Magento\Framework\Model\Entity\MetadataPool;
use Psr\Log\LoggerInterface;

/**
 * Class CommitCallback
 */
class CommitCallback
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CommitCallback constructor.
     *
     * @param MetadataPool $metadataPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPool $metadataPool,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->logger = $logger;
    }

    /**
     * @param $entityType
     * @throws \Exception
     */
    public function process($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $metadata->getEntityConnection();
        $hash = spl_object_hash($connection);
        if ($connection->getTransactionLevel() === 0) {
            $callbacks = CallbackPool::get($hash);
            try {
                foreach ($callbacks as $callback) {
                    call_user_func($callback);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), $e->getTrace());
                throw $e;
            }

        }
    }

    /**
     * @param string $entityType
     * @param array $callback
     * @throws \Exception
     */
    public function attach($entityType, $callback)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        CallbackPool::attach(spl_object_hash($metadata->getEntityConnection()), $callback);
    }

    /**
     * @param $entityType
     * @throws \Exception
     */
    public function clear($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        CallbackPool::clear(spl_object_hash($metadata->getEntityConnection()));
    }
}
