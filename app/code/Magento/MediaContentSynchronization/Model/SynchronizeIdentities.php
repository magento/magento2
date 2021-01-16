<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Model\SynchronizeIdentitiesPool;
use Psr\Log\LoggerInterface;

/**
 * Batch Synchronize content with assets
 */
class SynchronizeIdentities implements SynchronizeIdentitiesInterface
{
    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var SynchronizeIdentitiesPool
     */
    private $synchronizeIdentitiesPool;

    /**
     * @param LoggerInterface $log
     * @param SynchronizeIdentitiesPool $synchronizeIdentitiesPool
     */
    public function __construct(
        LoggerInterface $log,
        SynchronizeIdentitiesPool $synchronizeIdentitiesPool
    ) {
        $this->log = $log;
        $this->synchronizeIdentitiesPool = $synchronizeIdentitiesPool;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $mediaContentIdentities): void
    {
        $failed = [];

        foreach ($this->synchronizeIdentitiesPool->get() as $name => $synchronizer) {
            try {
                $synchronizer->execute($mediaContentIdentities);
            } catch (\Exception $exception) {
                $this->log->critical($exception);
                $failed[] = $name;
            }
        }

        if (!empty($failed)) {
            throw new LocalizedException(
                __(
                    'Failed to execute the following content synchronizers: %synchronizers',
                    [
                        'synchronizers' => implode(', ', $failed)
                    ]
                )
            );
        }
    }
}
