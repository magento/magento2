<?php
/**
 * Unit Test for \Magento\Framework\Filesystem\Directory\PathValidator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Directory;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;

class PathValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * \Magento\Framework\Filesystem\Driver
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\PathValidator
     */
    protected $pathValidator;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->driver = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);
        $this->pathValidator = new \Magento\Framework\Filesystem\Directory\PathValidator(
            $this->driver
        );
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->pathValidator = null;
    }

    /**
     * @param string $directoryPath
     * @param string $path
     * @param string $scheme
     * @param bool $absolutePath
     * @param string $prefix
     * @dataProvider validateDataProvider
     */
    public function testValidate($directoryPath, $path, $scheme, $absolutePath, $prefix)
    {
        $this->driver->expects($this->exactly(2))
            ->method('getRealPathSafety')
            ->willReturnMap(
                [
                    [$directoryPath, $directoryPath],
                    [null, $prefix . $directoryPath . ltrim($path, '/')],
                ]
            );

        $this->assertNull(
            $this->pathValidator->validate($directoryPath, $path, $scheme, $absolutePath)
        );
    }

    /**
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            ['/directory/path/', '/directory/path/', '/', false, '/://'],
            ['/directory/path/', '/var/.regenerate', null, false, ''],
        ];
    }
}
