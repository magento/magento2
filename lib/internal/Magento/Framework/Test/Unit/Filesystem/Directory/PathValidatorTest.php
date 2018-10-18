<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\PathValidator;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Test for Magento\Framework\Filesystem\Directory\PathValidator class.
 */
class PathValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DriverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $driverMock;

    /**
     * @var PathValidator
     */
    private $pathValidator;
    
    protected function setUp()
    {
        $this->driverMock = $this->getMockForAbstractClass(DriverInterface::class);
        
        $this->pathValidator = new PathValidator($this->driverMock);
    }

    /**
     * @return void
     */
    public function testValidateWithAbsolutePath()
    {
        $directoryPath = __DIR__ . '/pub/static/';
        $path = '/pub/static/testFile.txt';

        $this->driverMock->expects($this->at(0))
            ->method('getRealPathSafety')
            ->with($directoryPath)
            ->willReturn($directoryPath);
        $this->driverMock->expects($this->at(1))
            ->method('getRealPathSafety')
            ->with($path)
            ->willReturn($directoryPath);

        $this->pathValidator->validate($directoryPath, $path, null, true);
    }

    /**
     * @return void
     */
    public function testValidateWithoutAbsolutePath()
    {
        $directoryPath = __DIR__ . '/pub/static/';
        $path = '/pub/static/testFile.txt';
        $actualPath = __DIR__ . '/pub/static/';

        $this->driverMock->expects($this->at(0))
            ->method('getRealPathSafety')
            ->with($directoryPath)
            ->willReturn($directoryPath);
        $this->driverMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($directoryPath, $path, null)
            ->willReturn($actualPath);
        $this->driverMock->expects($this->at(2))
            ->method('getRealPathSafety')
            ->with($actualPath)
            ->willReturn($actualPath);

        $this->pathValidator->validate($directoryPath, $path);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessageRegExp #^Path ".+/static/testFile.txt" cannot be used with directory ".+/pub/static/"$#
     */
    public function testValidateWithWrongPath()
    {
        $directoryPath = __DIR__ . '/pub/static/';
        $path = '../../../pub/static/testFile.txt';
        $actualPath = __DIR__ . '/../../app/etc/';

        $this->driverMock->expects($this->at(0))
            ->method('getRealPathSafety')
            ->with($directoryPath)
            ->willReturn($directoryPath);
        $this->driverMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($directoryPath, $path, null)
            ->willReturn($actualPath);
        $this->driverMock->expects($this->at(2))
            ->method('getRealPathSafety')
            ->with($actualPath)
            ->willReturn($actualPath);

        $this->pathValidator->validate($directoryPath, $path);
    }
}
