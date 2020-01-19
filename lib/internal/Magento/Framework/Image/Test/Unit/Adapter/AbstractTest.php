<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\Image\Adapter\AbstractAdapter.
 */
namespace Magento\Framework\Image\Test\Unit\Adapter;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Image\Adapter\AbstractAdapter
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Filesystem\Directory\Write
     */
    protected $directoryWriteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Magento\Framework\Filesystem
     */
    protected $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject |\Psr\Log\LoggerInterface
     */
    protected $loggerMock;

    protected function setUp()
    {
        $this->directoryWriteMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->filesystemMock =
            $this->createPartialMock(\Magento\Framework\Filesystem::class, ['getDirectoryWrite', 'createDirectory']);
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnValue($this->directoryWriteMock)
        );
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $this->_model = $this->getMockForAbstractClass(
            \Magento\Framework\Image\Adapter\AbstractAdapter::class,
            [$this->filesystemMock, $this->loggerMock]
        );
    }

    protected function tearDown()
    {
        $this->directoryWriteMock = null;
        $this->_model = null;
        $this->filesystemMock = null;
        $this->loggerMock = null;
    }

    /**
     * Test adaptResizeValues with null as a value one of parameters
     *
     * @dataProvider adaptResizeValuesDataProvider
     */
    public function testAdaptResizeValues($width, $height, $expectedResult)
    {
        $method = new \ReflectionMethod($this->_model, '_adaptResizeValues');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $width, $height);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function adaptResizeValuesDataProvider()
    {
        $expected = [
            'src' => ['x' => 0, 'y' => 0],
            'dst' => ['x' => 0, 'y' => 0, 'width' => 135, 'height' => 135],
            'frame' => ['width' => 135, 'height' => 135],
        ];

        return [[135, null, $expected], [null, 135, $expected]];
    }

    /**
     * @dataProvider prepareDestinationDataProvider
     */
    public function testPrepareDestination($destination, $newName, $expectedResult)
    {
        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcPath');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcPath');

        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcName');
        $property->setAccessible(true);
        $property->setValue($this->_model, '_fileSrcName');

        $method = new \ReflectionMethod($this->_model, '_prepareDestination');
        $method->setAccessible(true);

        $result = $method->invoke($this->_model, $destination, $newName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function prepareDestinationDataProvider()
    {
        return [
            [__DIR__, 'name.txt', __DIR__ . '/name.txt'],
            [__DIR__ . '/name.txt', null, __DIR__ . '/name.txt'],
            [null, 'name.txt', '_fileSrcPath' . '/name.txt'],
            [null, null, '_fileSrcPath' . '/_fileSrcName']
        ];
    }
}
