<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Isolation\WorkingDirectory.
 */
namespace Magento\Test\Isolation;

class WorkingDirectoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Isolation\WorkingDirectory
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = new \Magento\TestFramework\Isolation\WorkingDirectory();
    }

    protected function tearDown(): void
    {
        $this->_object = null;
    }

    public function testStartTestEndTest()
    {
        $oldWorkingDir = getcwd();
        $newWorkingDir = __DIR__;
        if ($oldWorkingDir == $newWorkingDir) {
            $this->markTestSkipped("Test requires the current working directory to differ from '{$oldWorkingDir}'.");
        }
        $this->_object->startTest($this);
        chdir($newWorkingDir);
        $this->assertEquals($newWorkingDir, getcwd(), 'Unable to change the current working directory.');
        $this->_object->endTest($this);
        $this->assertEquals($oldWorkingDir, getcwd(), 'Current working directory was not restored.');
    }
}
