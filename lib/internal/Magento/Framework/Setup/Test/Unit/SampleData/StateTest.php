<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit\SampleData;

/**
 * Class StateTest
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Setup\SampleData\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $state;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeInterface;

    /**
     * @var string
     */
    protected $absolutePath;

    protected function setUp()
    {
        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->setMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeInterface = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['write', 'close']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->state = $objectManager->getObject(
            \Magento\Framework\Setup\SampleData\State::class,
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
            ->willReturn(\Magento\Framework\Setup\SampleData\State::ERROR);
        $this->state->setError();
        $this->assertTrue($this->state->hasError());
    }

    /**
     * Clear state file
     */
    protected function tearDown()
    {
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->any())->method('openFile')->willReturnSelf($this->absolutePath);

        $this->state->clearState();
    }
}
