<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Model;

use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;

/**
 * A pool that handles content and assets synchronization.
 * @see SynchronizeFilesInterface
 */
class SynchronizerPool
{
    /**
     * Content with assets synchronizers
     *
     * @var SynchronizeInterface[]
     */
    private $synchronizers;

    /**
     * @param SynchronizeInterface[] $synchronizers
     */
    public function __construct(
        array $synchronizers = []
    ) {
        foreach ($synchronizers as $synchronizer) {
            if (!$synchronizer instanceof SynchronizeInterface) {
                throw new \InvalidArgumentException(
                    get_class($synchronizer) . ' must implement ' . SynchronizeInterface::class
                );
            }
        }

        $this->synchronizers = $synchronizers;
    }

    /**
     * Get all synchronizers from the pool
     *
     * @return SynchronizeInterface[]
     */
    public function get(): array
    {
        return $this->synchronizers;
    }
}
