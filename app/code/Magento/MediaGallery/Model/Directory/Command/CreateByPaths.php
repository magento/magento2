<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Command;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Phrase;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Psr\Log\LoggerInterface;

/**
 * Create directories by provided paths in the media storage
 */
class CreateByPaths implements CreateDirectoriesByPathsInterface
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @param LoggerInterface $logger
     * @param Storage $storage
     * @param IsPathExcludedInterface $isPathExcluded
     * @param ScopeConfigInterface|null $coreConfig
     */
    public function __construct(
        LoggerInterface $logger,
        Storage $storage,
        IsPathExcludedInterface $isPathExcluded,
        ?ScopeConfigInterface $coreConfig = null
    ) {
        $this->logger = $logger;
        $this->storage = $storage;
        $this->isPathExcluded = $isPathExcluded;
        $this->coreConfig = $coreConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $failedPaths = [];
        $excludedPaths = [];
        foreach ($paths as $path) {
            if ($this->isPathExcluded->execute($path)) {
                $excludedPaths[] = $path;
                continue;
            }
            try {
                $path = $path !== null ? $path : '';
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $name = basename($path);
                //phpcs:ignore Magento2.Functions.DiscouragedFunction
                $folder = substr($path, 0, strrpos($path, $name));

                $this->storage->createDirectory(
                    $name,
                    $this->storage->getCmsWysiwygImages()->getStorageRoot() . $folder
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedPaths[] = $path;
            }
        }

        $messages = [];
        if (!empty($excludedPaths)) {
            $messages[] = $this->getExcludedPathsMessage($excludedPaths);
        }
        if (!empty($failedPaths)) {
            $messages[] = __('Could not create directories: %paths', ['paths' => implode(' ,', $failedPaths)]);
        }
        if (!empty($messages)) {
            throw new CouldNotSaveException(
                count($messages) === 1 ? $messages[0] : new Phrase('%1 %2', $messages)
            );
        }
    }

    /**
     * Describe restrictions separately from storage failures.
     *
     * @param string[] $paths
     * @return Phrase
     */
    private function getExcludedPathsMessage(array $paths): Phrase
    {
        $folders = $this->coreConfig->getValue(self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH, 'default');
        $folders = is_array($folders) ? $folders : [];
        $folders = array_filter($folders, static fn ($folder) => is_string($folder) && trim($folder) !== '');
        $folders = array_unique(array_map('trim', $folders));
        if (empty($folders)) {
            return __(
                'Could not create directories: %paths. These paths are outside the allowed Media Gallery folders.',
                ['paths' => implode(' ,', $paths)]
            );
        }

        return __(
            'Could not create directories: %paths. You can create folders only inside these folders: %allowedPaths.',
            ['paths' => implode(' ,', $paths), 'allowedPaths' => implode(', ', $folders)]
        );
    }
}
