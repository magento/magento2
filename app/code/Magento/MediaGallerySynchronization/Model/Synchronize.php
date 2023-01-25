<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeFilesInterface;
use Magento\MediaGallerySynchronizationApi\Api\SynchronizeInterface;
use Magento\MediaGallerySynchronizationApi\Model\SynchronizerPool;
use Psr\Log\LoggerInterface;

/**
 * Synchronize media storage and media assets database records
 */
class Synchronize implements SynchronizeInterface
{
    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var SynchronizerPool
     */
    private $synchronizerPool;

    /**
     * @var FetchMediaStorageFileBatches
     */
    private $batchGenerator;

    /**
     * @var ResolveNonExistedAssets
     */
    private $resolveNonExistedAssets;

    /**
     * @param ResolveNonExistedAssets $resolveNonExistedAssets
     * @param LoggerInterface $log
     * @param SynchronizerPool $synchronizerPool
     * @param FetchMediaStorageFileBatches $batchGenerator
     */
    public function __construct(
        ResolveNonExistedAssets $resolveNonExistedAssets,
        LoggerInterface $log,
        SynchronizerPool $synchronizerPool,
        FetchMediaStorageFileBatches $batchGenerator
    ) {
        $this->resolveNonExistedAssets = $resolveNonExistedAssets;
        $this->log = $log;
        $this->synchronizerPool = $synchronizerPool;
        $this->batchGenerator = $batchGenerator;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $failed = [];

        foreach ($this->synchronizerPool->get() as $name => $synchronizer) {
            if (!$synchronizer instanceof SynchronizeFilesInterface) {
                $failed[] = $name;
                continue;
            }
            foreach ($this->batchGenerator->execute() as $batch) {
                try {
                    $synchronizer->execute($batch);
                } catch (\Exception $exception) {
                    $this->log->critical($exception);
                    $failed[] = $name;
                }
            }
        }

        $this->resolveNonExistedAssets->execute();
        if (!empty($failed)) {
            throw new LocalizedException(
                __(
                    'Failed to execute the following synchronizers: %synchronizers',
                    [
                        'synchronizers' => implode(', ', $failed)
                    ]
                )
            );
        }
    }
}
