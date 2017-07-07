<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Shell\CommandRendererInterface;
use Magento\Framework\Shell\Driver;
use Magento\Framework\ShellInterface;
use Psr\Log\LoggerInterface;

/**
 * Class is separate from \Magento|Framework\Shell because logging behavior is different, and relies on ObjectManager
 * being available.
 */
class Shell implements ShellInterface
{
    /** @var Driver */
    private $driver;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Driver $driver
     * @param CommandRendererInterface $commandRenderer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Driver $driver,
        LoggerInterface $logger
    ) {
        $this->driver = $driver;
        $this->logger = $logger;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @throws \Magento\Framework\Exception\LocalizedException If a command returns non-zero exit code
     * @return string
     */
    public function execute($command, array $arguments = [])
    {
        try {
            $response = $this->driver->execute($command, $arguments);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getLogMessage());
            throw $e;
        }
        $escapedCommand = $response->getEscapedCommand();
        $output = $response->getOutput();
        $exitCode = $response->getExitCode();
        $logEntry = $escapedCommand . PHP_EOL . $output;
        if ($exitCode) {
            $this->logger->error($logEntry);
            $commandError = new \Exception($output, $exitCode);
            throw new LocalizedException(
                new Phrase("Command returned non-zero exit code:\n`%1`", [$command]),
                $commandError
            );
        }
        $this->logger->info($logEntry);
        return $output;
    }
}
