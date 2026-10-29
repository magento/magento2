<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\SampleData;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use Magento\Framework\Setup\SampleData\State;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    use MockCreationTrait;
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
     * @var WriteInterface|MockObject
     */
    protected $directoryWriteMock;

    /**
     * @var string
     */
    protected $absolutePath;

    protected function setUp(): void
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();
        
        // Directory WriteInterface (19 methods) - use createMock for all interface methods
        $this->directoryWriteMock = $this->createMock(WriteInterface::class);
        
        // File WriteInterface (what openFile returns) has write(), read(), and close() methods
        $this->writeInterface = $this->createMock(FileWriteInterface::class);
        
        // Configure directory mock: file exists and openFile returns file stream
        $this->directoryWriteMock->method('isExist')->willReturn(true);
        $this->directoryWriteMock->method('openFile')->willReturn($this->writeInterface);
        $this->directoryWriteMock->method('delete')->willReturn(true);
        
        $this->filesystem->method('getDirectoryWrite')->willReturn($this->directoryWriteMock);
        
        $objectManager = new ObjectManager($this);
        $this->state = $objectManager->getObject(
            State::class,
            ['filesystem' => $this->filesystem]
        );
    }

    public function testClearState()
    {
        // Test clearState - should not throw any exceptions
        $this->state->clearState();
    }

    /**
     * @covers \Magento\Framework\Setup\SampleData\State::setError
     */
    public function testHasError()
    {
        // Configure mocks for write and read operations
        $this->writeInterface->method('write')->willReturnSelf();
        $this->writeInterface->method('read')->willReturn(State::ERROR);
        
        $this->state->setError();
        $this->assertTrue($this->state->hasError());
    }

    /**
     * Clear state file
     */
    protected function tearDown(): void
    {
        unset($this->state, $this->filesystem, $this->directoryWriteMock, $this->writeInterface);
    }
}
