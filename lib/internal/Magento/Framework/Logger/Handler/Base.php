<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class \Magento\Framework\Logger\Handler\Base
 *
 */
class Base extends StreamHandler
{
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
     * @param string $filePath
     * @param string $fileName
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        $this->filesystem = $filesystem;
        if (!empty($fileName)) {
            $this->fileName = $this->sanitizeFileName($fileName);
        }
        parent::__construct(
            $filePath ? $filePath . $this->fileName : BP . DIRECTORY_SEPARATOR . $this->fileName,
            $this->loggerType
        );

        $this->setFormatter(new LineFormatter(null, null, true));
    }

    /**
     * @param string $fileName
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private function sanitizeFileName($fileName)
    {
        if (!is_string($fileName)) {
            throw  new \InvalidArgumentException('Filename expected to be a string');
        }

        $parts = explode('/', $fileName);
        $parts = array_filter($parts, function ($value) {
            return !in_array($value, ['', '.', '..']);
        });

        return implode('/', $parts);
    }

    /**
     * {@inheritDoc}
     *
     * @param $record array
     *
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
