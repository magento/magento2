<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Handler;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception as ExceptionHandler;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * System stream handler
 */
class System extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/system.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * @param DriverInterface $filesystem
     * @param ExceptionHandler $exceptionHandler
     * @param string|null $filePath
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        ExceptionHandler $exceptionHandler,
        ?string $filePath = null
    ) {
        $this->exceptionHandler = $exceptionHandler;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * Writes formatted record through the handler
     *
     * @param array $record The record metadata
     * @return void
     */
    public function write(LogRecord $record): void
    {
        if (isset($record['context']['exception'])) {
            $this->exceptionHandler->handle($record);

            return;
        }
        $record['formatted'] = $this->getFormatter()->format($record);

        parent::write($record);
    }
}
