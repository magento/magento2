<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\LoggerInterface;

/**
 * Web UI Logger
 *
 * @package Magento\Setup\Model
 * @since 2.0.0
 */
class WebLogger implements LoggerInterface
{
    /**
     * Log File
     *
     * @var string
     * @since 2.0.0
     */
    protected $logFile = 'install.log';

    /**
     * Currently open file resource
     *
     * @var Filesystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * Currently open file resource
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    protected $directory;

    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     * @since 2.0.0
     */
    private $isInline = false;

    /**
     * Constructor
     * @param Filesystem $filesystem
     * @param string $logFile
     * @since 2.0.0
     */
    public function __construct(Filesystem $filesystem, $logFile = null)
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
        if ($logFile) {
            $this->logFile = $logFile;
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logSuccess($message)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="text-success">[SUCCESS] ' . $message . '</span><br>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logError(\Exception $e)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="text-danger">[ERROR] ' . $e . '<span><br>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function log($message)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="text-info">' . $message . '</span><br>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logInline($message)
    {
        $this->isInline = true;
        $this->writeToFile('<span class="text-info">' . $message . '</span>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logMeta($message)
    {
        $this->terminateLine();
        $this->writeToFile('<span class="hidden">' . $message . '</span><br>');
    }

    /**
     * Write the message to file
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    private function writeToFile($message)
    {
        $this->directory->writeFile($this->logFile, $message, 'a+');
    }

    /**
     * Gets contents of the log
     *
     * @return array
     * @since 2.0.0
     */
    public function get()
    {
        $fileContents = explode(PHP_EOL, $this->directory->readFile($this->logFile));
        return $fileContents;
    }

    /**
     * Clears contents of the log
     *
     * @return void
     * @since 2.0.0
     */
    public function clear()
    {
        if ($this->directory->isExist($this->logFile)) {
            $this->directory->delete($this->logFile);
        }
    }

    /**
     * Checks existence of install.log file
     *
     * @return bool
     * @since 2.0.0
     */
    public function logfileExists()
    {
        return ($this->directory->isExist($this->logFile));
    }

    /**
     * Terminates line if the inline logging is started
     *
     * @return void
     * @since 2.0.0
     */
    private function terminateLine()
    {
        if ($this->isInline) {
            $this->isInline = false;
            $this->writeToFile('<br>');
        }
    }
}
