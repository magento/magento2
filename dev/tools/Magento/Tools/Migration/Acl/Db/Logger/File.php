<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db\Logger;

use InvalidArgumentException;

/**
 * Db migration logger. Output result put to file
 */
class File extends \Magento\Tools\Migration\Acl\Db\AbstractLogger
{
    /**
     * Path to log file
     *
     * @var string
     */
    protected $_file = null;

    /**
     * @param string $file
     * @throws InvalidArgumentException
     */
    public function __construct($file)
    {
        $logDir = realpath(__DIR__ . '/../../') . '/log/';
        if (false == is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        if (false == is_writeable($logDir)) {
            throw new InvalidArgumentException('Directory ' . dirname($logDir) . ' is not writeable');
        }

        if (empty($file)) {
            throw new InvalidArgumentException('Log file name is required');
        }
        $this->_file = $logDir . $file;
    }

    /**
     * Put report to file
     *
     * @return void
     */
    public function report()
    {
        file_put_contents($this->_file, (string)$this);
    }
}
