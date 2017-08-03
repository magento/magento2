<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Console Logger
 *
 * @package Magento\Setup\Model
 * @since 2.0.0
 */
class ConsoleLogger implements LoggerInterface
{
    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     * @since 2.0.0
     */
    private $isInline = false;

    /**
     * Console
     *
     * @var OutputInterface
     * @since 2.0.0
     */
    protected $console;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @since 2.0.0
     */
    public function __construct(OutputInterface $output)
    {
        $this->console = $output;
        $outputFormatter = $this->console->getFormatter();
        $outputFormatter->setStyle('detail', new OutputFormatterStyle('blue'));
        $outputFormatter->setStyle('metadata', new OutputFormatterStyle('cyan'));
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logSuccess($message)
    {
        $this->terminateLine();
        $this->console->writeln("<info>[SUCCESS]" . ($message ? ": $message" : '') . '</info>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logError(\Exception $e)
    {
        $this->terminateLine();
        $this->console->writeln("<error>[ERROR]: " . $e . '</error>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function log($message)
    {
        $this->terminateLine();
        $this->console->writeln('<detail>' . $message . '</detail>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logInline($message)
    {
        $this->isInline = true;
        $this->console->write('<detail>' . $message . '</detail>');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function logMeta($message)
    {
        $this->terminateLine();
        $this->console->writeln('<metadata>' . $message . '</metadata>');
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
            $this->console->writeln('');
        }
    }
}
