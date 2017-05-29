<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Process;

use Magento\Deploy\Process\Queue;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Service\DeployPackage;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Deployment Queue class unit tests
 *
 * @see Queue
 */
class QueueTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->appState = $this->getMock(AppState::class, [], [], '', false);
        $this->localeResolver = $this->getMockForAbstractClass(
            LocaleResolver::class,
            ['setLocale'],
            '',
            false
        );
        $this->resourceConnection = $this->getMock(
            ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->logger = $this->getMockForAbstractClass(
            LoggerInterface::class,
            ['notice', 'info'],
            '',
            false
        );
        $this->deployPackageService = $this->getMock(DeployPackage::class, ['deploy'], [], '', false);

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
        $package = $this->getMock(Package::class, [], [], '', false);
        $package->expects($this->once())->method('getPath')->willReturn('path');

        $this->assertEquals(true, $this->queue->add($package));
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
        $package = $this->getMock(Package::class, [], [], '', false);
        $package->expects($this->any())->method('getState')->willReturn(1);
        $package->expects($this->once())->method('getParent')->willReturn(null);
        $package->expects($this->any())->method('getArea')->willReturn('area');
        $package->expects($this->any())->method('getPath')->willReturn('path');
        $package->expects($this->any())->method('getFiles')->willReturn([]);

        $this->appState->expects($this->once())->method('emulateAreaCode');

        $this->queue->add($package, []);

        $this->resourceConnection->expects(self::never())->method('closeConnection');

        $this->assertEquals(0, $this->queue->process());
    }
}
