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
 * @since 2.0.0
 */
class System extends Base
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $fileName = '/var/log/system.log';

    /**
     * @var int
     * @since 2.0.0
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var Exception
     * @since 2.0.0
     */
    protected $exceptionHandler;

    /**
     * @param DriverInterface $filesystem
     * @param Exception $exceptionHandler
     * @param string $filePath
     * @since 2.0.0
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
     * @since 2.0.0
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
