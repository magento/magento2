<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationApi\Model;

use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;

class SynchronizeIdentitiesPool
{
    /**
     * Content with assets synchronizers
     *
     * @var SynchronizeIdentitiesInterface[]
     */
    private $synchronizers;

    /**
     * @param SynchronizeIdentitiesInterface[] $synchronizers
     */
    public function __construct(
        array $synchronizers = []
    ) {
        foreach ($synchronizers as $synchronizer) {
            if (!$synchronizer instanceof SynchronizeIdentitiesInterface) {
                throw new \InvalidArgumentException(
                    get_class($synchronizer) . ' must implement ' . SynchronizeIdentitiesInterface::class
                );
            }
        }

        $this->synchronizers = $synchronizers;
    }

    /**
     * Get all synchronizers from the pool
     *
     * @return SynchronizeIdentitiesInterface[]
     */
    public function get(): array
    {
        return $this->synchronizers;
    }
}
