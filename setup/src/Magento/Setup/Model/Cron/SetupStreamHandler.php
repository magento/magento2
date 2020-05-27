<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Setup specific stream handler
 *
 * @deprecated Starting from Magento 2.3.6 Web Setup Wizard is deprecated
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
