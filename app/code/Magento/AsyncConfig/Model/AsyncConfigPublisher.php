<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;

class AsyncConfigPublisher implements \Magento\AsyncConfig\Api\AsyncConfigPublisherInterface
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
     * @var \Magento\Framework\Filesystem\DirectoryList
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
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param File $file
     */
    public function __construct(
        AsyncConfigMessageInterfaceFactory $asyncConfigFactory,
        PublisherInterface $publisher,
        Json $json,
        \Magento\Framework\Filesystem\DirectoryList $dir,
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
            if (!empty($data['value']['tmp_name'])) {
                $newPath =
                    $this->dir->getPath(DirectoryList::MEDIA) . '/' .
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    pathinfo($data['value']['tmp_name'])['filename'];
                $this->file->mv(
                    $data['value']['tmp_name'],
                    $newPath
                );
                $data['value']['tmp_name'] = $newPath;
            }
        }
    }
}
