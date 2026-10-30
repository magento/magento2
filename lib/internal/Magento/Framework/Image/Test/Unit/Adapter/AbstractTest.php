<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\Image\Adapter\AbstractAdapter.
 */
namespace Magento\Framework\Image\Test\Unit\Adapter;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Image\Adapter\AbstractAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AbstractTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var AbstractAdapter
     */
    protected $_model;

    /**
     * @var MockObject|Write
     */
    protected $directoryWriteMock;

    /**
     * @var MockObject|Filesystem
     */
    protected $filesystemMock;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->directoryWriteMock = $this->createMock(Write::class);
        $this->filesystemMock = $this->createPartialMockWithReflection(
            Filesystem::class,
            ['createDirectory', 'getDirectoryWrite']
        );
        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->willReturn(
            $this->directoryWriteMock
        );
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->_model = $this->getMockBuilder(AbstractAdapter::class)
            ->setConstructorArgs([$this->filesystemMock, $this->loggerMock])
            ->getMock();
    }

    protected function tearDown(): void
    {
        $this->directoryWriteMock = null;
        $this->_model = null;
        $this->filesystemMock = null;
        $this->loggerMock = null;
    }

    /**
     * Test adaptResizeValues with null as a value one of parameters
     *     */
    #[DataProvider('adaptResizeValuesDataProvider')]
    public function testAdaptResizeValues($width, $height, $expectedResult)
    {
        $method = new \ReflectionMethod($this->_model, '_adaptResizeValues');

        $result = $method->invoke($this->_model, $width, $height);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function adaptResizeValuesDataProvider()
    {
        $expected = [
            'src' => ['x' => 0, 'y' => 0],
            'dst' => ['x' => 0, 'y' => 0, 'width' => 135, 'height' => 135],
            'frame' => ['width' => 135, 'height' => 135],
        ];

        return [[134.5, null, $expected], [null, 134.5, $expected]];
    }

    /**     */
    #[DataProvider('prepareDestinationDataProvider')]
    public function testPrepareDestination($destination, $newName, $expectedResult)
    {
        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcPath');
        $property->setValue($this->_model, '_fileSrcPath');

        $property = new \ReflectionProperty(get_class($this->_model), '_fileSrcName');
        $property->setValue($this->_model, '_fileSrcName');

        $method = new \ReflectionMethod($this->_model, '_prepareDestination');

        $result = $method->invoke($this->_model, $destination, $newName);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public static function prepareDestinationDataProvider()
    {
        return [
            [__DIR__, 'name.txt', __DIR__ . '/name.txt'],
            [__DIR__ . '/name.txt', null, __DIR__ . '/name.txt'],
            [null, 'name.txt', '_fileSrcPath' . '/name.txt'],
            [null, null, '_fileSrcPath' . '/_fileSrcName']
        ];
    }
}
