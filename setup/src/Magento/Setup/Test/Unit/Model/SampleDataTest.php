<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\SampleData;

/**
 * Test Magento\Setup\Model\SampleData
 */
class SampleDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\SampleData
     */
    protected $sampleDataInstall;

    /**
     * @var \Magento\Framework\Setup\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerInterface;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryList;

    protected function setUp()
    {
        $this->loggerInterface = $this->getMock('Magento\Framework\Setup\LoggerInterface');
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->sampleDataInstall = new SampleData($this->directoryList);
    }

    public function testIsDeployed()
    {
        $this->directoryList->expects($this->once())->method('getPath')->with('code');
        $this->sampleDataInstall->isDeployed();
    }

    /**
     * Test SampleData installation check method.
     * Can be tested only negative case because file_exists method used in the tested class
     */
    public function testIsInstalledSuccessfully()
    {
        $this->assertFalse($this->sampleDataInstall->isInstalledSuccessfully());
    }

    public function testIsInstallationError()
    {
        $this->directoryList->expects($this->once())->method('getPath')->with('code')->willReturn(null);
        $this->assertFalse($this->sampleDataInstall->isInstalledSuccessfully());
    }
}
