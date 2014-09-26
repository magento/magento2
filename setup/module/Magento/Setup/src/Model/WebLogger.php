<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Model;

/**
 * Web UI Logger
 *
 * @package Magento\Setup\Model
 */
class WebLogger implements LoggerInterface
{
    /**
     * Log File
     *
     * @var string
     */
    protected $logFile = 'install.log';

    /**
     * Currently open file resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Whether the log contains an error message
     *
     * @var bool
     */
    protected $hasError = false;

    public function __construct()
    {
        $this->logFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->logFile;
    }

    /**
     * Opens log file in the specified mode
     *
     * @param string $mode
     * @return void
     */
    private function open($mode)
    {
        $this->resource = @fopen($this->logFile, $mode);
    }

    /**
     * Closes the log file
     *
     * @return void
     */
    private function close()
    {
        fclose($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function logSuccess($message)
    {
        $this->writeToFile('<span class="text-success">[SUCCESS] ' . $message . '</span>');
    }

    /**
     * {@inheritdoc}
     */
    public function logError(\Exception $e)
    {
        $this->writeToFile('<span class="text-danger">[ERROR] ' . $e . '<span>');
    }

    /**
     * {@inheritdoc}
     */
    public function log($message)
    {
        $this->writeToFile('<span class="text-info">' . $message . '</span>');
    }

    /**
     * Write the message to file
     *
     * @param string $message
     * @return void
     */
    private function writeToFile($message)
    {
        $this->open('a+');
        fwrite($this->resource, $message . PHP_EOL);
        $this->close();
    }

    /**
     * Whether there is an error in the log
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->hasError;
    }

    /**
     * Gets contents of the log
     *
     * @return array
     */
    public function get()
    {
        $this->open('r+');
        fseek($this->resource, 0);
        $messages = [];
        while (($string = fgets($this->resource)) !== false) {
            if (strpos($string, '[ERROR]') !== false) {
                $this->hasError = true;
            }
            $messages[] = $string;
        }
        $this->close();
        return $messages;
    }

    /**
     * Clears contents of the log
     *
     * @return void
     */
    public function clear()
    {
        @unlink($this->logFile);
    }
}
