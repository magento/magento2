<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Plugin;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryRenditions\Model\Config;
use Magento\MediaGalleryRenditionsApi\Api\GenerateRenditionsInterface;
use Magento\MediaGalleryRenditionsApi\Api\GetRenditionPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Intercept and set renditions path on PrepareImage
 */
class SetRenditionPath
{
    /**
     * @var GetRenditionPathInterface
     */
    private $getRenditionPath;

    /**
     * @var GenerateRenditionsInterface
     */
    private $generateRenditions;

    /**
     * @var Images
     */
    private $imagesHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param GetRenditionPathInterface $getRenditionPath
     * @param GenerateRenditionsInterface $generateRenditions
     * @param Images $imagesHelper
     * @param Config $config
     * @param LoggerInterface $log
     */
    public function __construct(
        GetRenditionPathInterface $getRenditionPath,
        GenerateRenditionsInterface $generateRenditions,
        Images $imagesHelper,
        Config $config,
        LoggerInterface $log
    ) {
        $this->getRenditionPath = $getRenditionPath;
        $this->generateRenditions = $generateRenditions;
        $this->imagesHelper = $imagesHelper;
        $this->config = $config;
        $this->log = $log;
    }

    /**
     * Replace the original asset path with rendition path
     *
     * @param GetInsertImageContent $subject
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        GetInsertImageContent $subject,
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): array {
        $arguments = [
            $encodedFilename,
            $forceStaticPath,
            $renderAsTag,
            $storeId
        ];

        if (!$this->config->isEnabled() || !$this->config->isMediaGalleryEnabled()) {
            return $arguments;
        }

        $path = $this->imagesHelper->idDecode($encodedFilename);

        try {
            $this->generateRenditions->execute([$path]);
        } catch (LocalizedException $exception) {
            $this->log->error($exception);
            return $arguments;
        }

        $arguments[0] = $this->imagesHelper->idEncode($this->getRenditionPath->execute($path));

        return $arguments;
    }
}
