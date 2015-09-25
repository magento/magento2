<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Test\Unit\Model;

/**
 * Class StateTest
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SampleData\Model\State|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $this->absolutePath = BP . '/var/sample-data-state.flag';
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->setMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->writeInterface = $this->getMockForAbstractClass(
            'Magento\Framework\Filesystem\Directory\WriteInterface',
            [],
            '',
            false,
            true,
            true,
            ['getAbsolutePath']
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->state = $objectManager->getObject(
            'Magento\SampleData\Model\State',
            ['filesystem' => $this->filesystem]
        );
    }

    public function testClearState()
    {
        $this->filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->any())->method('getAbsolutePath')->willReturn($this->absolutePath);

        $this->state->clearState();
    }

    /**
     * @covers setError()
     */
    public function testHasError()
    {
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->once())->method('getAbsolutePath')->willReturn($this->absolutePath);

        $this->state->setError();
        $this->assertTrue($this->state->hasError());
    }

    /**
     * Clear state file
     */
    protected function tearDown()
    {
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($this->writeInterface);
        $this->writeInterface->expects($this->once())->method('getAbsolutePath')->willReturn($this->absolutePath);

        $this->state->clearState();    }

}