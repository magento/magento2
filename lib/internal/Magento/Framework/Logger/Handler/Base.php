<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Handler;

use InvalidArgumentException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\LogRecord;

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
     * @param FormatterInterface|null $formatter Defaults to a LineFormatter that includes stack traces
     */
    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null,
        ?FormatterInterface $formatter = null
    ) {
        $this->filesystem = $filesystem;

        if (!empty($fileName)) {
            $this->fileName = $this->sanitizeFileName($fileName);
        }

        parent::__construct(
            $filePath ? $filePath . $this->fileName : BP . DIRECTORY_SEPARATOR . $this->fileName,
            $this->loggerType
        );

        $this->setFormatter($formatter ?? $this->createDefaultFormatter());
    }

    /**
     * Create the formatter used when none was injected
     *
     * Stack traces are included so that a Throwable reported through the PSR-3 reserved
     * $context['exception'] key stays diagnosable, instead of being reduced to its throw site.
     *
     * @return FormatterInterface
     */
    private function createDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter(
            format: null,
            dateFormat: null,
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: false,
            includeStacktraces: true
        );
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
    protected function write(LogRecord $record): void
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
