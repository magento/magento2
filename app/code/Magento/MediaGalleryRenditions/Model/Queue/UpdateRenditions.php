<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model\Queue;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryRenditionsApi\Api\GenerateRenditionsInterface;
use Psr\Log\LoggerInterface;

/**
 * Renditions update queue consumer.
 */
class UpdateRenditions
{
    private const RENDITIONS_DIRECTORY_NAME = '.renditions';

    /**
     * @var GenerateRenditionsInterface
     */
    private $generateRenditions;

    /**
     * @var FetchRenditionPathsBatches
     */
    private $fetchRenditionPathsBatches;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param GenerateRenditionsInterface $generateRenditions
     * @param FetchRenditionPathsBatches $fetchRenditionPathsBatches
     * @param LoggerInterface $log
     */
    public function __construct(
        GenerateRenditionsInterface $generateRenditions,
        FetchRenditionPathsBatches $fetchRenditionPathsBatches,
        LoggerInterface $log
    ) {
        $this->generateRenditions = $generateRenditions;
        $this->fetchRenditionPathsBatches = $fetchRenditionPathsBatches;
        $this->log = $log;
    }

    /**
     * Update renditions for given paths, if empty array is provided - all renditions are updated
     *
     * @param array $paths
     * @throws LocalizedException
     */
    public function execute(array $paths): void
    {
        if (!empty($paths)) {
            $this->updateRenditions($paths);
            return;
        }

        foreach ($this->fetchRenditionPathsBatches->execute() as $renditionPaths) {
            $this->updateRenditions($renditionPaths);
        }
    }

    /**
     * Update renditions and log exceptions
     *
     * @param string[] $renditionPaths
     */
    private function updateRenditions(array $renditionPaths): void
    {
        try {
            $this->generateRenditions->execute($this->getAssetPaths($renditionPaths));
        } catch (LocalizedException $exception) {
            $this->log->error($exception);
        }
    }

    /**
     * Get asset paths based on rendition paths
     *
     * @param string[] $renditionPaths
     * @return string[]
     */
    private function getAssetPaths(array $renditionPaths): array
    {
        $paths = [];

        foreach ($renditionPaths as $renditionPath) {
            try {
                $paths[] = $this->getAssetPath($renditionPath);
            } catch (\Exception $exception) {
                $this->log->error($exception);
            }
        }

        return $paths;
    }

    /**
     * Get asset path based on rendition path
     *
     * @param string $renditionPath
     * @return string
     * @throws LocalizedException
     */
    private function getAssetPath(string $renditionPath): string
    {
        if (strpos($renditionPath, self::RENDITIONS_DIRECTORY_NAME) !== 0) {
            throw new LocalizedException(
                __(
                    'Incorrect rendition path provided for update: %path',
                    [
                        'path' => $renditionPath
                    ]
                )
            );
        }

        return substr($renditionPath, strlen(self::RENDITIONS_DIRECTORY_NAME));
    }
}
