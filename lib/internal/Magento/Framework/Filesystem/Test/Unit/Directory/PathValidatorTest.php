<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Directory;

use Magento\Framework\Filesystem\Directory\PathValidator;
use Magento\Framework\Filesystem\Driver\File;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Framework\Filesystem\Directory\PathValidator
 */
class PathValidatorTest extends TestCase
{
    /**
     * \Magento\Framework\Filesystem\Driver
     *
     * @var MockObject
     */
    protected $driver;

    /**
     * @var PathValidator
     */
    protected $pathValidator;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->driver = $this->createMock(File::class);
        $this->pathValidator = new PathValidator(
            $this->driver
        );
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
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
                    [$directoryPath, rtrim($directoryPath, DIRECTORY_SEPARATOR)],
                    [null, $prefix . $directoryPath . ltrim($path, DIRECTORY_SEPARATOR)],
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
            ['/directory/path/', '/var/image - 1.jpg', null, false, ''],
        ];
    }
}
