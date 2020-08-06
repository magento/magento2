<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Model;

use Magento\MediaContentSynchronizationApi\Api\SynchronizerInterface;

/**
 * A pool that handles content and assets synchronization.
 * @see SynchronizeFilesInterface
 */
class SynchronizerPool
{
    /**
     * Content with assets synchronizers
     *
     * @var SynchronizerInterface[]
     */
    private $synchronizers;

    /**
     * @param SynchronizerInterface[] $synchronizers
     */
    public function __construct(
        array $synchronizers = []
    ) {
        foreach ($synchronizers as $synchronizer) {
            if (!$synchronizer instanceof SynchronizerInterface) {
                throw new \InvalidArgumentException(
                    get_class($synchronizer) . ' must implement ' . SynchronizerInterface::class
                );
            }
        }

        $this->synchronizers = $synchronizers;
    }

    /**
     * Get all synchronizers from the pool
     *
     * @return SynchronizerInterface[]
     */
    public function get(): array
    {
        return $this->synchronizers;
    }
}
