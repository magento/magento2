<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Used for extracting media asset list from a media content by the search pattern.
 */
class ExtractAssetFromContent
{
    /**
     * @var string
     */
    private $searchPatterns;

    /**
     * @var GetByPathInterface
     */
    private $getMediaAssetByPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param GetByPathInterface $getMediaAssetByPath
     * @param LoggerInterface $logger
     * @param array $searchPatterns
     */
    public function __construct(
        GetByPathInterface $getMediaAssetByPath,
        LoggerInterface $logger,
        array $searchPatterns
    ) {
        $this->getMediaAssetByPath = $getMediaAssetByPath;
        $this->logger = $logger;
        $this->searchPatterns = $searchPatterns;
    }

    /**
     * Search for the media asset in content and extract it providing a list of media assets.
     *
     * @param string $content
     * @return AssetInterface[]
     */
    public function execute(string $content): array
    {
        $paths = [];

        foreach ($this->searchPatterns as $pattern) {
            preg_match_all($pattern, $content, $matches, PREG_PATTERN_ORDER);
            if (!empty($matches[1])) {
                $paths += array_unique($matches[1]);
            }
        }

        return $this->getAssetsByPaths(array_unique($paths));
    }

    /**
     * Get media assets by paths array
     *
     * @param array $paths
     * @return AssetInterface[]
     */
    private function getAssetsByPaths(array $paths): array
    {
        $assets = [];

        foreach ($paths as $path) {
            try {
                /** @var AssetInterface $asset */
                $asset = $this->getMediaAssetByPath->execute('/' . $path);
                $assets[$asset->getId()] = $asset;
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        return $assets;
    }
}
