<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
interface ShellInterface
{
    /**
     * Execute a command through the command line, passing properly escaped arguments
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @throws \Magento\Framework\Exception If a command returns non-zero exit code
     * @return string
     */
    public function execute($command, array $arguments = []);
}
