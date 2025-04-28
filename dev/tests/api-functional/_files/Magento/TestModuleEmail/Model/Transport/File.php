<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleEmail\Model\Transport;

use Symfony\Component\Mime\Message as SymfonyMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;

class File implements TransportInterface
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
     * @var SendmailTransport|null
     */
    private ?SendmailTransport $transport=null;

    /**
     * @param Filesystem $filesystem
     * @param Json $json
     */
    public function __construct(
        Filesystem $filesystem,
        Json $json,
        SendmailTransport $transport
    ) {
        $this->filesystem = $filesystem;
        $this->json = $json;
        $this->transport = $transport;
    }

    /**
     * @param SymfonyMessage|RawMessage $message
     * @param Envelope|null $envelope
     * @inheritDoc
     * @throws FileSystemException
     */
    public function send(SymfonyMessage|RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $config = $this->json->unserialize($directory->readFile(self::CONFIG_FILE));
        $directory = $this->filesystem->getDirectoryWrite($config['directory']);
        $mail = $message->toString();
        $addresses = $this->message->getHeaders()->get('To')?->getAddresses() ?? [];
        foreach ($addresses as $address) {
            $index = 1;
            $filename = preg_replace('/[^a-z0-9_]/', '__', strtolower($address->getEmail()));
            $basePath = $config['path']. DIRECTORY_SEPARATOR . $filename;
            $path = $basePath . '.eml';
            while ($directory->isExist($path)) {
                $path = $basePath . '_' . ($index++) . '.eml';
            }
            $directory->writeFile($path, $mail);
        }

        return $this->transport->send($message, $envelope);
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

    /**
     * To String
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->transport) {
            return (string) $this->transport;
        }

        return 'smtp://sendmail';
    }
}
