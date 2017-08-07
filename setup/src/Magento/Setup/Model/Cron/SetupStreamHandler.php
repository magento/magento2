<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Setup specific stream handler
 * @since 2.1.0
 */
class SetupStreamHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $fileName = '/var/log/update.log';

    /**
     * @var int
     * @since 2.1.0
     */
    protected $loggerType = \Magento\Framework\Logger\Monolog::ERROR;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @since 2.1.0
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);
    }
}
