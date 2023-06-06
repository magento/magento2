<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleEmail\Model\Transport;

use Laminas\Mail;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;

class File implements \Laminas\Mail\Transport\TransportInterface
{
    private const CONFIG_FILE = 'mail-transport-config.json';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @param Filesystem $filesystem
     * @param Json $json
     */
    public function __construct(
        Filesystem $filesystem,
        Json $json
    ) {
        $this->filesystem = $filesystem;
        $this->json = $json;
    }

    /**
     * @inheritDoc
     */
    public function send(Mail\Message $message)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $config = $this->json->unserialize($directory->readFile(self::CONFIG_FILE));
        $directory = $this->filesystem->getDirectoryWrite($config['directory']);
        $mail = $message->toString();
        foreach ($message->getTo() as $address) {
            $index = 1;
            $filename = preg_replace('/[^a-z0-9_]/', '__', strtolower($address->getEmail()));
            $basePath = $config['path']. DIRECTORY_SEPARATOR . $filename;
            $path = $basePath . '.eml';
            while ($directory->isExist($path)) {
                $path = $basePath . '_' . ($index++) . '.eml';
            }
            $directory->writeFile($path, $mail);
        }
    }

    /**
     * Finds whether "file" mail transport is enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function isEnabled(): bool
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        return $directory->isExist(self::CONFIG_FILE);
    }
}
