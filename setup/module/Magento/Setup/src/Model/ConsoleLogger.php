<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Zend\Console\ColorInterface;
use Zend\Console\Console;

/**
 * Console Logger
 *
 * @package Magento\Setup\Model
 */
class ConsoleLogger implements LoggerInterface
{
    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     */
    private $isInline = false;

    /**
     * Console
     *
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    protected $console;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->console = Console::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function logSuccess($message)
    {
        $this->terminateLine();
        $this->console->writeLine("[SUCCESS]" . ($message ? ": $message" : ''), ColorInterface::LIGHT_GREEN);
    }

    /**
     * {@inheritdoc}
     */
    public function logError(\Exception $e)
    {
        $this->terminateLine();
        $this->console->writeLine("[ERROR]: " . $e, ColorInterface::LIGHT_RED);
    }

    /**
     * {@inheritdoc}
     */
    public function log($message)
    {
        $this->terminateLine();
        $this->console->writeLine($message, ColorInterface::LIGHT_BLUE);
    }

    /**
     * {@inheritdoc}
     */
    public function logInline($message)
    {
        $this->isInline = true;
        $this->console->write($message, ColorInterface::LIGHT_BLUE);
    }

    /**
     * {@inheritdoc}
     */
    public function logMeta($message)
    {
        $this->terminateLine();
        $this->console->writeLine($message, ColorInterface::GRAY);
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
            $this->console->writeLine('');
        }
    }
}
