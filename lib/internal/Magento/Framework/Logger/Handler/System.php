<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Monolog\Logger;

/**
 * Class \Magento\Framework\Logger\Handler\System
 *
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
     * @var Exception
     */
    protected $exceptionHandler;

    /**
     * @param DriverInterface $filesystem
     * @param Exception $exceptionHandler
     * @param string $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        Exception $exceptionHandler,
        $filePath = null
    ) {
        $this->exceptionHandler = $exceptionHandler;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * Writes formatted record through the handler.
     *
     * @param $record array The record metadata
     * @return void
     */
    public function write(array $record)
    {
        if (isset($record['context']['exception'])) {
            $this->exceptionHandler->handle($record);

            return;
        }

        $record['formatted'] = $this->getFormatter()->format($record);

        parent::write($record);
    }
}
