<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Base extends StreamHandler
{
    const DEFAULT_LOG_DIRECTORY = '/var/log';

    /**
     * @var string
     */
    protected $fileDirectory;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var DriverInterface
     */
    protected $filesystem;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath Full path to the log file
     * @param string $fileDirectory Directory relative to the Magento root directory
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileDirectory = null
    ) {
        $this->filesystem = $filesystem;
        $this->fileDirectory = $fileDirectory;
        parent::__construct(
            $filePath ? $filePath : BP . self::DEFAULT_LOG_DIRECTORY . $this->fileDirectory . DIRECTORY_SEPARATOR . $this->fileName,
            $this->loggerType
        );
        $this->setFormatter(new LineFormatter(null, null, true));
    }

    /**
     * @{inheritDoc}
     *
     * @param $record array
     * @return void
     */
    public function write(array $record)
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);
        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);
    }
}
