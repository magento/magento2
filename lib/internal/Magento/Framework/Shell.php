<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Shell\CommandRendererInterface;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 * @since 2.0.0
 */
class Shell implements ShellInterface
{
    /**
     * Logger instance
     *
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var CommandRendererInterface
     * @since 2.0.0
     */
    private $commandRenderer;

    /**
     * @param CommandRendererInterface $commandRenderer
     * @param \Psr\Log\LoggerInterface $logger Logger instance to be used to log commands and their output
     * @since 2.0.0
     */
    public function __construct(
        CommandRendererInterface $commandRenderer,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
        $this->commandRenderer = $commandRenderer;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @return string Output of an executed command
     * @throws \Magento\Framework\Exception\LocalizedException If a command returns non-zero exit code
     * @since 2.0.0
     */
    public function execute($command, array $arguments = [])
    {
        $command = $this->commandRenderer->render($command, $arguments);
        $this->log($command);

        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled)) {
            throw new Exception\LocalizedException(new \Magento\Framework\Phrase("exec function is disabled."));
        }

        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        $this->log($output);
        if ($exitCode) {
            $commandError = new \Exception($output, $exitCode);
            throw new Exception\LocalizedException(
                new \Magento\Framework\Phrase("Command returned non-zero exit code:\n`%1`", [$command]),
                $commandError
            );
        }
        return $output;
    }

    /**
     * Log a message, if a logger is specified
     *
     * @param string $message
     * @return void
     * @since 2.0.0
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }
}
