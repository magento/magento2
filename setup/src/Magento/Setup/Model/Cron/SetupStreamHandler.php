<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Monolog\Handler\StreamHandler;

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
}
