<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\AsyncConfigPublisherInterface;
use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Filesystem\DirectoryList as FilesystemDirectoryList;

class AsyncConfigPublisher implements AsyncConfigPublisherInterface
{
    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var AsyncConfigMessageInterfaceFactory
     */
    private $asyncConfigFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var FilesystemDirectoryList
     */
    private $dir;

    /**
     * @var File
     */
    private $file;

    /**
     *
     * @param AsyncConfigMessageInterfaceFactory $asyncConfigFactory
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param FilesystemDirectoryList $dir
     * @param File $file
     */
    public function __construct(
        AsyncConfigMessageInterfaceFactory $asyncConfigFactory,
        PublisherInterface $publisher,
        Json $json,
        FilesystemDirectoryList $dir,
        File $file
    ) {
        $this->asyncConfigFactory = $asyncConfigFactory;
        $this->messagePublisher = $publisher;
        $this->serializer = $json;
        $this->dir = $dir;
        $this->file = $file;
    }

    /**
     * @inheritDoc
     */
    public function saveConfigData(array $configData)
    {
        $asyncConfig = $this->asyncConfigFactory->create();
        $this->saveImages($configData);
        $asyncConfig->setConfigData($this->serializer->serialize($configData));
        $this->messagePublisher->publish('async_config.saveConfig', $asyncConfig);
    }

    /**
     * Save Images to temporary Path
     *
     * @param array $configData
     * @return void
     * @throws FileSystemException
     */
    private function saveImages(array &$configData)
    {
        if (isset($configData['groups']['placeholder'])) {
            $this->changeImagePath($configData['groups']['placeholder']['fields']);
        } elseif (isset($configData['groups']['identity'])) {
            $this->changeImagePath($configData['groups']['identity']['fields']);
        }
    }

    /**
     * Change Placeholder Data path if exists
     *
     * @param array $fields
     * @return void
     * @throws FileSystemException
     */
    private function changeImagePath(array &$fields)
    {
        foreach ($fields as &$data) {
            if (!isset($data['value']) || !is_array($data['value'])) {
                continue;
            }
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (!empty($data['value']['tmp_name']) && is_uploaded_file($data['value']['tmp_name'])) {
                $newPath =
                    $this->dir->getPath(DirectoryList::MEDIA) . '/' .
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    pathinfo($data['value']['tmp_name'])['filename'];
                $this->file->mv(
                    $data['value']['tmp_name'],
                    $newPath
                );
                $data['value']['tmp_name'] = $newPath;
            } elseif (array_key_exists('tmp_name', $data['value'])) {
                unset($data['value']['tmp_name']);
            }
        }
    }
}
