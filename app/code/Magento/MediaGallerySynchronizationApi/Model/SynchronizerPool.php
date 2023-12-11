<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationApi\Model;

use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;

/**
 * A pool of Media storage to database synchronizers
 * @see SynchronizeFilesInterface
 */
class SynchronizerPool
{
    /**
     * Media storage to database synchronizers
     *
     * @var SynchronizeFilesInterface[]
     */
    private $synchronizers;

    /**
     * @param SynchronizeFilesInterface[] $synchronizers
     */
    public function __construct(array $synchronizers = [])
    {
        foreach ($synchronizers as $name => $synchronizer) {
            if (!$synchronizer instanceof SynchronizeFilesInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Synchronizer %s must implement %s.',
                    $name,
                    SynchronizeFilesInterface::class
                ));
            }
        }
        $this->synchronizers = $synchronizers;
    }

    /**
     * Get all synchronizers from the pool
     *
     * @return SynchronizeFilesInterface[]
     */
    public function get(): array
    {
        return $this->synchronizers;
    }
}
