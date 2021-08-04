<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Process;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Service\DeployPackage;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Deployment Queue class unit tests
 *
 * @see Queue
 */
class QueueTest extends TestCase
{
    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var LocaleResolver|Mock
     */
    private $localeResolver;

    /**
     * @var ResourceConnection|Mock
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    /**
     * @var DeployPackage
     */
    private $deployPackageService;

    /**
     * @var DeployStaticFile|Mock
     */
    private $deployStaticFile;

    /**
     * @var Package|Mock
     */
    private $package;

    /**
     * @var PackageFile|Mock
     */
    private $packageFile;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->package = $this->createMock(Package::class);
        $this->deployStaticFile = $this->createMock(DeployStaticFile::class);

        $this->packageFile = $this->createMock(PackageFile::class);
        $this->packageFile
            ->expects($this->any())
            ->method('getContent')
            ->willReturn('{}');

        $this->localeResolver = $this->getMockForAbstractClass(
            LocaleResolver::class,
            ['setLocale'],
            '',
            false
        );

        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['notice', 'info'],
            '',
            false
        );

        $configScope = $this->createMock(ScopeInterface::class);
        $this->appState = new AppState(
            $configScope
        );

        $this->deployPackageService = new DeployPackage(
            $this->appState,
            $this->localeResolver,
            $this->deployStaticFile,
            $this->logger
        );
    }

    /**
     * @see Queue:add()
     */
    public function testAdd()
    {
        $queue = new Queue(
            $this->appState,
            $this->localeResolver,
            $this->resourceConnection,
            $this->logger,
            $this->deployPackageService,
            [],
            0
        );

        $this->package->expects($this->once())->method('getPath')->willReturn('path');
        $this->assertTrue($queue->add($this->package));
        $packages = $queue->getPackages();
        $this->assertEquals(
            $this->package,
            isset($packages['path']['package']) ? $packages['path']['package'] : null
        );
    }

    /**
     * @see Queue::process()
     */
    public function testProcess()
    {
        $queue = new Queue(
            $this->appState,
            $this->localeResolver,
            $this->resourceConnection,
            $this->logger,
            $this->deployPackageService,
            [],
            0
        );

        $this->package->expects($this->any())->method('getState')->willReturn(0);
        $this->package->expects($this->exactly(2))->method('getParent')->willReturn(true);
        $this->package->expects($this->any())->method('getArea')->willReturn('global');
        $this->package->expects($this->any())->method('getPath')->willReturn('path');
        $this->package->expects($this->any())->method('getFiles')->willReturn([]);
        $this->package->expects($this->any())->method('getPreProcessors')->willReturn([]);
        $this->package->expects($this->any())->method('getPostProcessors')->willReturn([]);
        $this->logger->expects($this->exactly(3))->method('info')->willReturnSelf();
        $queue->add($this->package, []);
        $this->resourceConnection->expects(self::never())->method('closeConnection');
        $this->assertEquals(0, $queue->process());
    }

    /**
     * @see Queue::process()
     * @dataProvider maxProcessesDataProvider
     */
    public function testProcessFailedPackagesToThrowAnException($maxProcesses)
    {
        $this->deployStaticFile
            ->expects($this->any())
            ->method('writeFile')
            ->willThrowException(new \Exception);

        $queue = new Queue(
            $this->appState,
            $this->localeResolver,
            $this->resourceConnection,
            $this->logger,
            $this->deployPackageService,
            [],
            $maxProcesses
        );

        $this->package->expects($this->any())->method('getState')->willReturn(0);
        $this->package->expects($this->any())->method('getParent')->willReturn(true);
        $this->package->expects($this->any())->method('getArea')->willReturn('global');
        $this->package->expects($this->any())->method('getPath')->willReturn('path');
        $this->package->expects($this->any())->method('getFiles')->willReturn([$this->packageFile]);
        $this->package->expects($this->any())->method('getPreProcessors')->willReturn([]);
        $this->package->expects($this->any())->method('getPostProcessors')->willReturn([]);
        $this->logger->expects($this->any())->method('info')->willReturnSelf();
        $queue->add($this->package, []);
        $this->expectException(\RuntimeException::class);
        $queue->process();
    }

    /**
     * @return int[]
     */
    public function maxProcessesDataProvider(): array
    {
        return [
            [0],
            [1]
        ];
    }

}
