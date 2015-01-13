<?php
/**
 * Unit Test for \Magento\Framework\Filesystem\Directory\Write
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

class WriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * \Magento\Framework\Filesystem\Driver
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $write;

    /**
     * \Magento\Framework\Filesystem\File\ReadFactory
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->driver = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->fileFactory = $this->getMock(
            'Magento\Framework\Filesystem\File\WriteFactory',
            [],
            [],
            '',
            false
        );
        $this->write = new \Magento\Framework\Filesystem\Directory\Write(
            $this->fileFactory,
            $this->driver,
            null,
            'cool-permissions'
        );
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->driver = null;
        $this->fileFactory = null;
        $this->write = null;
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf(
            'Magento\Framework\Filesystem\DriverInterface',
            $this->write->getDriver(),
            'getDriver method expected to return instance of Magento\Framework\Filesystem\DriverInterface'
        );
    }

    public function testCreate()
    {
        $this->driver->expects($this->once())->method('isDirectory')->will($this->returnValue(false));
        $this->driver->expects($this->once())->method('createDirectory')->will($this->returnValue(true));

        $this->assertTrue($this->write->create('correct-path'));
    }

    public function testIsWritable()
    {
        $this->driver->expects($this->once())->method('isWritable')->will($this->returnValue(true));
        $this->assertTrue($this->write->isWritable('correct-path'));
    }
}
