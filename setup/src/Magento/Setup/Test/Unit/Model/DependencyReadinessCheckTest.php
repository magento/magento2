<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Composer\RequireUpdateDryRunCommand
     */
    private $reqUpdDryRunCommand;

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
        $this->composerJsonFinder =
            $this->getMock(\Magento\Framework\Composer\ComposerJsonFinder::class, [], [], '', false);
        $this->composerJsonFinder->expects($this->once())->method('findComposerJson')->willReturn('composer.json');
        $this->directoryList =
            $this->getMock(\Magento\Framework\App\Filesystem\DirectoryList::class, [], [], '', false);
        $this->directoryList->expects($this->exactly(2))->method('getPath')->willReturn('var');
        $this->reqUpdDryRunCommand =
            $this->getMock(\Magento\Composer\RequireUpdateDryRunCommand::class, [], [], '', false);
        $this->file = $this->getMock(\Magento\Framework\Filesystem\Driver\File::class, [], [], '', false);
        $this->file->expects($this->once())->method('copy')->with('composer.json', 'var/composer.json');
        $composerAppFactory = $this->getMock(
            \Magento\Framework\Composer\MagentoComposerApplicationFactory::class,
            [],
            [],
            '',
            false
        );
        $composerAppFactory->expects($this->once())
            ->method('createRequireUpdateDryRunCommand')
            ->willReturn($this->reqUpdDryRunCommand);
        $this->dependencyReadinessCheck = new DependencyReadinessCheck(
            $this->composerJsonFinder,
            $this->directoryList,
            $this->file,
            $composerAppFactory
        );
    }

    public function testRunReadinessCheckFailed()
    {
        $this->reqUpdDryRunCommand->expects($this->once())
            ->method('run')
            ->with([], 'var')
            ->willThrowException(new \RuntimeException('Failed' . PHP_EOL . 'dependency readiness check'));
        $expected = ['success' => false, 'error' => 'Failed<br/>dependency readiness check'];
        $this->assertEquals($expected, $this->dependencyReadinessCheck->runReadinessCheck([]));
    }

    public function testRunReadinessCheck()
    {
        $this->reqUpdDryRunCommand->expects($this->once())
            ->method('run')
            ->with([], 'var')
            ->willReturn('Success');
        $expected = ['success' => true];
        $this->assertEquals($expected, $this->dependencyReadinessCheck->runReadinessCheck([]));
    }
}
