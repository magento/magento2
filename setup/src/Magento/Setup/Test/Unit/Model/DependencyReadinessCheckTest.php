<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Composer\RequireUpdateDryRunCommand;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Setup\Model\DependencyReadinessCheck;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DependencyReadinessCheckTest extends TestCase
{
    /**
     * @var MockObject|ComposerJsonFinder
     */
    private $composerJsonFinder;

    /**
     * @var MockObject|DirectoryList
     */
    private $directoryList;

    /**
     * @var MockObject|RequireUpdateDryRunCommand
     */
    private $reqUpdDryRunCommand;

    /**
     * @var MockObject|File
     */
    private $file;

    /**
     * @var DependencyReadinessCheck;
     */
    private $dependencyReadinessCheck;

    protected function setUp(): void
    {
        $this->composerJsonFinder =
            $this->createMock(ComposerJsonFinder::class);
        $this->composerJsonFinder->expects($this->once())->method('findComposerJson')->willReturn('composer.json');
        $this->directoryList =
            $this->createMock(DirectoryList::class);
        $this->directoryList->expects($this->exactly(2))->method('getPath')->willReturn('var');
        $this->reqUpdDryRunCommand =
            $this->createMock(RequireUpdateDryRunCommand::class);
        $this->file = $this->createMock(File::class);
        $this->file->expects($this->once())->method('copy')->with('composer.json', 'var/composer.json');
        $composerAppFactory = $this->createMock(MagentoComposerApplicationFactory::class);
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
