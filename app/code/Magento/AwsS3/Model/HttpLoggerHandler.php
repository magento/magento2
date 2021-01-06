<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Model;

use Aws\Handler\GuzzleV6\GuzzleHandler;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class HttpLoggerHandler
{
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var string
     */
    private $file;

    /**
     * @param Filesystem $filesystem
     * @param string $file
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        $file = 'debug/s3.log'
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->file = $file;
    }

    public function __invoke()
    {
        $this->directory->create(pathinfo($this->file, PATHINFO_DIRNAME));
        $localStream = $this->directory->getDriver()->fileOpen($this->directory->getAbsolutePath($this->file), 'a');
        $streamHandler = new StreamHandler($localStream, Logger::DEBUG, true, null, true);
        $logger = new \Monolog\Logger('S3', [$streamHandler]);
        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{code}:{method}:{target} {error}')
            )
        );
        return new GuzzleHandler(new Client(['handler' => $stack]));
    }
}
