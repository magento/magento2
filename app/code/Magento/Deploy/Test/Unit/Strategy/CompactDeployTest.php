<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Strategy;

use Magento\Deploy\Strategy\CompactDeploy;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackagePool;
use Magento\Deploy\Process\Queue;

use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Compact deployment service class implementation unit tests
 *
 * @see CompactDeploy
 */
class CompactDeployTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->options = [
            'opt1' => '',
            'opt2' => ''
        ];

        $virtualPackage = $this->getMock(Package::class, [], [], '', false);
        $virtualPackage->expects($this->exactly(1))
            ->method('isVirtual')
            ->willReturn(true);
        $virtualPackage->expects($this->atLeastOnce())
            ->method('getParentPackages')
            ->willReturn([]);
        $virtualPackage->expects($this->never())
            ->method('setParam')
            ->willReturn('virtual');

        $realPackage = $this->getMock(Package::class, [], [], '', false);
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
        $this->packagePool = $this->getMock(
            PackagePool::class,
            ['getPackagesForDeployment'],
            [],
            '',
            false
        );
        $this->packagePool->expects($this->once())
            ->method('getPackagesForDeployment')
            ->with($this->options)
            ->willReturn($this->packages);

        $this->queue = $this->getMock(
            Queue::class,
            ['add', 'process'],
            [],
            '',
            false
        );
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
