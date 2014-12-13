<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     */
    private $isInline = false;

    /**
     * Constructor
     */
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
        $this->resource = fopen($this->logFile, $mode);
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
        $this->terminateLine();
        $this->writeToFile('<span class="text-success">[SUCCESS] ' . $message . '</span><br/>');
    }

    /**
     * {@inheritdoc}
     */
    public function logError(\Exception $e)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="text-danger">[ERROR] ' . $e . '<span><br/>');
    }

    /**
     * {@inheritdoc}
     */
    public function log($message)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="text-info">' . $message . '</span><br/>');
    }

    /**
     * {@inheritdoc}
     */
    public function logInline($message)
    {
        $this->isInline = true;
        $this->writeToFile('<span class="text-info">' . $message . '</span>');
    }

    /**
     * {@inheritdoc}
     */
    public function logMeta($message)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="hidden">' . $message . '</span><br/>');
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
        fwrite($this->resource, $message);
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
        unlink($this->logFile);
    }

    /**
     * Terminates line if the inline logging is started
     *
     * @return void
     */
    private function terminateLine()
    {
        if ($this->isInline) {
            $this->isInline = false;
            $this->writeToFile('</br>');
        }
    }
}
