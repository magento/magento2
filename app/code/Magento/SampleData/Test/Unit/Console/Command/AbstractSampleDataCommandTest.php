<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SampleData\Test\Unit\Console\Command;

use Composer\Console\Application;
use Composer\Console\ApplicationFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\SampleData\Model\Dependency;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArrayInputFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractSampleDataCommandTest extends TestCase
{
    /**
     * @var ReadInterface|MockObject
     */
    protected $directoryReadMock;

    /**
     * @var WriteInterface|MockObject
     */
    protected $directoryWriteMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var Dependency|MockObject
     */
    protected $sampleDataDependencyMock;

    /**
     * @var ArrayInputFactory|MockObject
     */
    protected $arrayInputFactoryMock;

    /**
     * @var Application|MockObject
     */
    protected $applicationMock;

    /**
     * @var ApplicationFactory|MockObject
     */
    protected $applicationFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->directoryWriteMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->sampleDataDependencyMock = $this->createMock(Dependency::class);
        $this->arrayInputFactoryMock = $this->createMock(ArrayInputFactory::class);
        $this->applicationMock = $this->createMock(Application::class);
        $this->applicationFactoryMock = $this->createPartialMock(ApplicationFactory::class, ['create']);
    }

    /**
     * @param array $sampleDataPackages     Array in form [package_name => version_constraint]
     * @param string $pathToComposerJson    Fake path to composer.json
     * @param int $appRunResult             Composer exit code
     * @param array $additionalComposerArgs Additional arguments that composer expects
     */
    protected function setupMocks(
        $sampleDataPackages,
        $pathToComposerJson,
        $appRunResult,
        $additionalComposerArgs = []
    ) {
        $this->directoryReadMock->expects($this->any())->method('getAbsolutePath')->willReturn($pathToComposerJson);
        $this->directoryReadMock->expects($this->any())->method('readFile')->with('composer.json')->willReturn(
            '{"version": "0.0.1"}'
        );
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::ROOT)->willReturn(
            $this->directoryReadMock
        );
        $this->sampleDataDependencyMock->expects($this->any())->method('getSampleDataPackages')->willReturn(
            $sampleDataPackages
        );
        $this->arrayInputFactoryMock->expects($this->never())->method('create');

        $this->applicationMock->expects($this->any())
            ->method('run')
            ->with(
                new ArrayInput(
                    array_merge(
                        $this->expectedComposerArguments(
                            $sampleDataPackages,
                            $pathToComposerJson
                        ),
                        $additionalComposerArgs
                    )
                ),
                $this->anything()
            )
            ->willReturn($appRunResult);

        if (($appRunResult !== 0) && !empty($sampleDataPackages)) {
            $this->applicationMock->expects($this->once())->method('resetComposer')->willReturnSelf();
        }

        $this->applicationFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->applicationMock);
    }

    /**
     * Expected arguments for composer based on sample data packages and composer.json path
     *
     * @param array $sampleDataPackages
     * @param string $pathToComposerJson
     * @return array
     */
    abstract protected function expectedComposerArguments(
        array $sampleDataPackages,
        string $pathToComposerJson
    ) : array;
}
