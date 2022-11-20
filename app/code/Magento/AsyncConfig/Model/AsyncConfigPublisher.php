<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $varDirectory;

    /**
     *
     * @param AsyncConfigMessageInterfaceFactory $asyncConfigFactory
     * @param PublisherInterface $publisher
     * @param Json $json
     * @param \Magento\Framework\Filesystem|null $filesystem
     */
    public function __construct(
        AsyncConfigMessageInterfaceFactory $asyncConfigFactory,
        PublisherInterface $publisher,
        Json $json,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->asyncConfigFactory = $asyncConfigFactory;
        $this->messagePublisher = $publisher;
        $this->serializer = $json;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * @inheritDoc
     */
    public function saveConfigData(array $configData)
    {
        $asyncConfig = $this->asyncConfigFactory->create();
        if ($configData['groups']['placeholder']) {
            foreach ($configData['groups']['placeholder']['fields'] as &$data) {
                if ($data['value']['tmp_name']) {
                    $newPath = $this->varDirectory->getAbsolutePath(DirectoryList::TMP) . '/' . pathinfo($data['value']['tmp_name'])['filename'];
                    move_uploaded_file(
                        $data['value']['tmp_name'],
                        $newPath
                    );
                    $data['value']['tmp_name'] = $newPath;
                }
            }
        }
        $asyncConfig->setConfigData($this->serializer->serialize($configData));
        $this->messagePublisher->publish('async_config.saveConfig', $asyncConfig);
    }
}
