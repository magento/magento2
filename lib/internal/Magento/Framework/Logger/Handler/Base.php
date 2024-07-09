<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Handler;

use InvalidArgumentException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Base stream handler
 *
 * @api
 */
class Base extends StreamHandler implements ResetAfterRequestInterface
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
     * @param string|null $filePath
     * @param string|null $fileName
     */
    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null
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
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->close();
    }

    /**
     * Remove dots from file name
     *
     * @param string $fileName
     * @return string
     */
    private function sanitizeFileName(string $fileName): string
    {
        $parts = explode('/', $fileName);
        $parts = array_filter($parts, function ($value) {
            return !in_array($value, ['', '.', '..']);
        });

        return implode('/', $parts);
    }

    /**
     * @inheritDoc
     */
    protected function write(array $record): void
    {
        $logDir = $this->filesystem->getParentDirectory($this->url);

        if (!$this->filesystem->isDirectory($logDir)) {
            $this->filesystem->createDirectory($logDir);
        }

        parent::write($record);
    }

    /**
     * Retrieve debug info
     *
     * @return string[]
     */
    public function __debugInfo()
    {
        return ['fileName' => $this->fileName];
    }
}
