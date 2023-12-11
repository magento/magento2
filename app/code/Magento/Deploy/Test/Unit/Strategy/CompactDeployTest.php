<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Strategy;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;
use Magento\Deploy\Strategy\CompactDeploy;
use PHPUnit\Framework\MockObject\MockObject as Mock;

use PHPUnit\Framework\TestCase;

/**
 * Compact deployment service class implementation unit tests
 *
 * @see CompactDeploy
 */
class CompactDeployTest extends TestCase
{
    /**
     * @var CompactDeploy
     */
    private $strategy;

    /**
     * Mock of package pool object
     *
     * @var PackagePool|Mock
     */
    private $packagePool;

    /**
     * Mock of deployment queue object
     *
     * @var Queue|Mock
     */
    private $queue;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $packages = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->options = [
            'opt1' => '',
            'opt2' => ''
        ];

        $virtualPackage = $this->createMock(Package::class);
        $virtualPackage->expects($this->exactly(1))
            ->method('isVirtual')
            ->willReturn(true);
        $virtualPackage->expects($this->atLeastOnce())
            ->method('getParentPackages')
            ->willReturn([]);
        $virtualPackage->expects($this->never())
            ->method('setParam')
            ->willReturn('virtual');

        $realPackage = $this->createMock(Package::class);
        $realPackage->expects($this->exactly(1))
            ->method('isVirtual')
            ->willReturn(false);
        $realPackage->expects($this->atLeastOnce())
            ->method('getParentPackages')
            ->willReturn([]);
        $realPackage->expects($this->exactly(1))
            ->method('setParam')
            ->willReturn('virtual');

        $this->packages = [
            'virtual' => $virtualPackage,
            'real' => $realPackage
        ];
        $this->packagePool = $this->createPartialMock(PackagePool::class, ['getPackagesForDeployment']);
        $this->packagePool->expects($this->once())
            ->method('getPackagesForDeployment')
            ->with($this->options)
            ->willReturn($this->packages);

        $this->queue = $this->createPartialMock(Queue::class, ['add', 'process']);
        $this->queue->expects($this->exactly(2))->method('add');
        $this->queue->expects($this->exactly(1))->method('process');

        $this->strategy = new CompactDeploy(
            $this->packagePool,
            $this->queue
        );
    }

    /**
     * @see CompactDeploy::deploy()
     */
    public function testDeploy()
    {
        $this->assertEquals(
            $this->packages,
            $this->strategy->deploy($this->options)
        );
    }
}
