<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\DependencyReadinessCheck;

class DependencyReadinessCheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Composer\ComposerJsonFinder
     */
    private $composerJsonFinder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Composer\MagentoComposerApplication
     */
    private $composerApp;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * @var DependencyReadinessCheck;
     */
    private $dependencyReadinessCheck;

    public function setUp()
    {
        $this->composerJsonFinder = $this->getMock('Magento\Framework\Composer\ComposerJsonFinder', [], [], '', false);
        $this->composerJsonFinder->expects($this->once())->method('findComposerJson')->willReturn('composer.json');
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->directoryList->expects($this->exactly(2))->method('getPath')->willReturn('var');
        $this->composerApp = $this->getMock('Magento\Composer\MagentoComposerApplication', [], [], '', false);
        $this->file = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->file->expects($this->once())->method('copy')->with('composer.json', 'var/composer.json');
        $composerAppFactory = $this->getMock(
            'Magento\Framework\Composer\MagentoComposerApplicationFactory',
            [],
            [],
            '',
            false
        );
        $composerAppFactory->expects($this->once())->method('create')->willReturn($this->composerApp);
        $this->dependencyReadinessCheck = new DependencyReadinessCheck(
            $this->composerJsonFinder,
            $this->directoryList,
            $this->file,
            $composerAppFactory
        );
    }

    public function testRunReadinessCheckFailed()
    {
        $this->composerApp->expects($this->at(0))
            ->method('runComposerCommand')
            ->with(['command' => 'require', 'packages' => [], '--no-update' => true], 'var');
        $this->composerApp->expects($this->at(1))
            ->method('runUpdateDryRun')
            ->with([], 'var')
            ->willThrowException(new \RuntimeException('Failed' . PHP_EOL . 'dependency readiness check'));
        $expected = ['success' => false, 'error' => 'Failed<br/>dependency readiness check'];
        $this->assertEquals($expected, $this->dependencyReadinessCheck->runReadinessCheck([]));
    }

    public function testRunReadinessCheck()
    {
        $this->composerApp->expects($this->at(0))
            ->method('runComposerCommand')
            ->with(['command' => 'require', 'packages' => [], '--no-update' => true], 'var');
        $this->composerApp->expects($this->at(1))
            ->method('runUpdateDryRun')
            ->with([], 'var')
            ->willReturn('Success');
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->dependencyReadinessCheck->runReadinessCheck([]));
    }
}
