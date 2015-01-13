<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Logger;

/**
 * Migration logger. Output result put to file
 */
class File extends \Magento\Tools\Migration\System\Configuration\AbstractLogger
{
    /**
     * Path to log file
     *
     * @var string
     */
    protected $_file = null;

    /**
     * @var \Magento\Tools\Migration\System\FileManager
     */
    protected $_fileManager;

    /**
     * @param string $file
     * @param \Magento\Tools\Migration\System\FileManager $fileManger
     * @throws \InvalidArgumentException
     */
    public function __construct($file, \Magento\Tools\Migration\System\FileManager $fileManger)
    {
        $this->_fileManager = $fileManger;

        $logDir = realpath(__DIR__ . '/../../') . '/log/';

        if (empty($file)) {
            throw new \InvalidArgumentException('Log file name is required');
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
        $this->_fileManager->write($this->_file, (string)$this);
    }
}
