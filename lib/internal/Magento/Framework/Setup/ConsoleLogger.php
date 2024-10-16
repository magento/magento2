<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ConsoleLogger implements ConsoleLoggerInterface
{
    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     */
    private $isInline = false;

    /**
     * @var OutputInterface
     */
    protected $console;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->console = $output;
        $outputFormatter = $this->console->getFormatter();
        $outputFormatter->setStyle('detail', new OutputFormatterStyle('blue'));
        $outputFormatter->setStyle('metadata', new OutputFormatterStyle('cyan'));
    }

    /**
     * @inheritdoc
     */
    public function logSuccess($message)
    {
        $this->terminateLine();
        $this->console->writeln("<info>[SUCCESS]" . ($message ? ": $message" : '') . '</info>');
    }

    /**
     * @inheritdoc
     */
    public function logError(\Exception $e)
    {
        $this->terminateLine();
        $this->console->writeln("<error>[ERROR]: " . $e . '</error>');
    }

    /**
     * @inheritdoc
     */
    public function log($message)
    {
        $this->terminateLine();
        $this->console->writeln('<detail>' . $message . '</detail>');
    }

    /**
     * @inheritdoc
     */
    public function logInline($message)
    {
        $this->isInline = true;
        $this->console->write('<detail>' . $message . '</detail>');
    }

    /**
     * @inheritdoc
     */
    public function logMeta($message)
    {
        $this->terminateLine();
        $this->console->writeln('<metadata>' . $message . '</metadata>');
    }

    /**
     * @inheritdoc
     */
    public function logMetaInline($message)
    {
        $this->isInline = true;
        $this->console->write('<metadata>' . $message . '</metadata>');
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
            $this->console->writeln('');
        }
    }
}
