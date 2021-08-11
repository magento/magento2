<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Process;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Service\DeployPackage;
use Magento\Framework\App\ResourceConnection;

use Magento\Framework\App\State as AppState;
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
     * @var Queue
     */
    private $queue;

    /**
     * @var AppState|Mock
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
     * @var DeployPackage|Mock
     */
    private $deployPackageService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->appState = $this->createMock(AppState::class);
        $this->localeResolver = $this->getMockForAbstractClass(
            LocaleResolver::class,
            ['setLocale'],
            '',
            false
        );
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['notice', 'info'],
            '',
            false
        );
        $this->deployPackageService = $this->createPartialMock(DeployPackage::class, ['deploy']);

        $this->queue = new Queue(
            $this->appState,
            $this->localeResolver,
            $this->resourceConnection,
            $this->logger,
            $this->deployPackageService,
            [],
            1
        );
    }

    /**
     * @see Queue:add()
     */
    public function testAdd()
    {
        $package = $this->createMock(Package::class);
        $package->expects($this->once())->method('getPath')->willReturn('path');

        $this->assertTrue($this->queue->add($package));
        $packages = $this->queue->getPackages();
        $this->assertEquals(
            $package,
            isset($packages['path']['package']) ? $packages['path']['package'] : null
        );
    }

    /**
     * @see Queue::process()
     */
    public function testProcess()
    {
        $package = $this->createMock(Package::class);
        $package->expects($this->any())->method('getState')->willReturn(0);
        $package->expects($this->exactly(2))->method('getParent')->willReturn(true);
        $package->expects($this->any())->method('getArea')->willReturn('area');
        $package->expects($this->any())->method('getPath')->willReturn('path');
        $package->expects($this->any())->method('getFiles')->willReturn([]);
        $this->logger->expects($this->exactly(2))->method('info')->willReturnSelf();

        $this->appState->expects($this->once())->method('emulateAreaCode');

        $this->queue->add($package, []);

        $this->resourceConnection->expects(self::never())->method('closeConnection');

        $this->assertEquals(0, $this->queue->process());
    }
}
