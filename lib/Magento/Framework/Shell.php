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
namespace Magento\Framework;

use Magento\Framework\Shell\CommandRendererInterface;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
class Shell implements ShellInterface
{
    /**
     * Logger instance
     *
     * @var \Zend_Log
     */
    protected $logger;

    /**
     * @var CommandRendererInterface
     */
    private $commandRenderer;

    /**
     * @param CommandRendererInterface $commandRenderer
     * @param \Zend_Log $logger Logger instance to be used to log commands and their output
     */
    public function __construct(
        CommandRendererInterface $commandRenderer,
        \Zend_Log $logger = null
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
     * @throws \Magento\Framework\Exception If a command returns non-zero exit code
     */
    public function execute($command, array $arguments = array())
    {
        $command = $this->commandRenderer->render($command, $arguments);
        $this->log($command);
        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        $this->log($output);
        if ($exitCode) {
            $commandError = new \Exception($output, $exitCode);
            throw new \Magento\Framework\Exception("Command `{$command}` returned non-zero exit code.", 0, $commandError);
        }
        return $output;
    }

    /**
     * Log a message, if a logger is specified
     *
     * @param string $message
     * @return void
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->log($message, \Zend_Log::INFO);
        }
    }
}
