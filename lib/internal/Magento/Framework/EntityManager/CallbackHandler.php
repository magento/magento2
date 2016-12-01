<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager;

use Magento\Framework\Model\CallbackPool;
use Psr\Log\LoggerInterface;

/**
 * Class CallbackHandler
 */
class CallbackHandler
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
     * CallbackHandler constructor.
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
     * @param string $entityType
     * @throws \Exception
     * @return void
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
     * @return void
     */
    public function attach($entityType, $callback)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        CallbackPool::attach(spl_object_hash($metadata->getEntityConnection()), $callback);
    }

    /**
     * @param string $entityType
     * @throws \Exception
     * @return void
     */
    public function clear($entityType)
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        CallbackPool::clear(spl_object_hash($metadata->getEntityConnection()));
    }
}
