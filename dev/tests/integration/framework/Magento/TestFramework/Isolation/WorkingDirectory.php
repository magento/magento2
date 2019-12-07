<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Isolation of the current working directory changes between tests
 */
namespace Magento\TestFramework\Isolation;

class WorkingDirectory
{
    /**
     * @var string
     */
    private $_currentWorkingDir;

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->_currentWorkingDir = getcwd();
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        if (getcwd() != $this->_currentWorkingDir) {
            chdir($this->_currentWorkingDir);
        }
    }
}
