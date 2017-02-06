<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Setup specific stream handler
 */
class SetupStreamHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/update.log';

    /**
     * @var int
     */
    protected $loggerType = \Magento\Framework\Logger\Monolog::ERROR;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);
    }
}
