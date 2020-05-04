<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\SampleData;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Setup\SampleData\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    protected $state;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var WriteInterface|MockObject
     */
    protected $writeInterface;

    /**
     * @var string
     */
    protected $absolutePath;

    protected function setUp(): void
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeInterface = $this->getMockForAbstractClass(
            WriteInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['write', 'close']
        );
        $objectManager = new ObjectManager($this);
        $this->state = $objectManager->getObject(
            State::class,
            ['filesystem' => $this->filesystem]
        );
    }

    public function testClearState()
    {
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->any())->method('openFile')->willReturnSelf();

        $this->state->clearState();
    }

    /**
     * @covers \Magento\Framework\Setup\SampleData\State::setError
     */
    public function testHasError()
    {
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->any())->method('openFile')->willReturnSelf();
        $this->writeInterface->expects($this->any())->method('write')->willReturnSelf();
        $this->writeInterface->expects($this->any())->method('close');
        $this->writeInterface->expects($this->any())->method('isExist')->willReturn(true);
        $this->writeInterface->expects($this->any())->method('read')
            ->willReturn(State::ERROR);
        $this->state->setError();
        $this->assertTrue($this->state->hasError());
    }

    /**
     * Clear state file
     */
    protected function tearDown(): void
    {
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->any())->method('openFile')->willReturnSelf($this->absolutePath);

        $this->state->clearState();
    }
}
