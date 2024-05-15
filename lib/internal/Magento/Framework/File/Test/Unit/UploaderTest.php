<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Uploader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\TargetDirectory;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Unit Test class for \Magento\Framework\File\Uploader
 */
class UploaderTest extends TestCase
{
    /**
     * @var Uploader
     */
    private $uploader;

    /**
     * Allowed extensions array
     *
     * @var array
     */
    private $_allowedMimeTypes = [
        'php' => 'text/plain',
        'txt' => 'text/plain'
    ];

    protected function setUp(): void
    {
        $class = new \ReflectionObject($this);
        $fileName = $class->getFilename();
        $fileType = 'php';
        $this->setupFiles(1230123, $fileName, $fileType);

        $driverPool  =  $this->createMock(DriverPool::class);
        $directoryList = $this->createMock(DirectoryList::class);
        $filesystem    = $this->createMock(Filesystem::class);
        $targetDirectory = $this->createMock(TargetDirectory::class);

        $this->uploader = new Uploader(
            "fileId",
            null,
            $directoryList,
            $driverPool,
            $targetDirectory,
            $filesystem
        );
        $this->uploader->setAllowedExtensions(array_keys($this->_allowedMimeTypes));
    }

    /**
     * @param string $fileName
     * @param string|bool $expectedCorrectedFileName
     *
     * @dataProvider getCorrectFileNameProvider
     */
    public function testGetCorrectFileName($fileName, $expectedCorrectedFileName)
    {
        $isExceptionExpected = $expectedCorrectedFileName === true;

        if ($isExceptionExpected) {
            $this->expectException(\LengthException::class);
        }

        $this->assertEquals(
            $expectedCorrectedFileName,
            Uploader::getCorrectFileName($fileName)
        );
    }

    /**
     * @return array
     */
    public function getCorrectFileNameProvider()
    {
        return [
            [
                '^&*&^&*^$$$$()',
                'file.'
            ],
            [
                '^&*&^&*^$$$$().png',
                'file.png'
            ],
            [
                '_',
                'file.'
            ],
            [
                '_.jpg',
                'file.jpg'
            ],
            [
                'a.' . str_repeat('b', 88),
                'a.' . str_repeat('b', 88)
            ],
            [
                'a.' . str_repeat('b', 256),
                true
            ]
        ];
    }

    /**
     * @param string $extension
     * @param bool $isValid
     *
     * @dataProvider checkAllowedExtensionProvider
     */
    public function testCheckAllowedExtension(bool $isValid, string $extension)
    {
        $this->assertEquals(
            $isValid,
            $this->uploader->checkAllowedExtension($extension)
        );
    }

    /**
     * @return array
     */
    public function checkAllowedExtensionProvider(): array
    {
        return [
            [
                true,
                'txt'
            ],
            [
                false,
                'png'
            ],
            [
                false,
                '$#@$#@$3'
            ],
            [
                false,
                '4324324324txt'
            ],
            [
                false,
                '$#$#$jpeg..$#2$#@$#@$'
            ],
            [
                false,
                '../../txt'
            ],
            [
                true,
                'php'
            ]
        ];
    }

    /**
     * Setup global variable $_FILES.
     *
     * @param int $fileSize
     * @param string $fileName
     * @param string $fileType
     * @return void
     */
    private function setupFiles($fileSize, $fileName, $fileType)
    {
        $_FILES = [
            'fileId' => [
                'name' => $fileName,
                'type' => $fileType,
                'tmp_name' => $fileName,
                'error' => 0,
                'size' => $fileSize,
            ]
        ];
    }
}
