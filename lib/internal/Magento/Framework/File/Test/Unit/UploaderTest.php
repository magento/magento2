<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Uploader;
use Magento\Framework\App\ObjectManager;

/**
 * Unit Test class for \Magento\Framework\File\Uploader
 */
class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Uploader
     */
    private $uploader;

    protected function setUp(): void
    {
        $this->uploader= ObjectManager::getInstance()->get(Uploader::class);
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
            $this->expectException(\InvalidArgumentException::class);
        }

        $this->assertEquals(
            $expectedCorrectedFileName,
            \Magento\Framework\File\Uploader::getCorrectFileName($fileName)
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
                'a.' . str_repeat('b', 89),
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
                'jpeg'
            ],
            [
                false,
                '$#@$#@$3'
            ],
            [
                true,
                '4324324324jpeg'
            ],
            [
                false,
                '$#$#$jpeg..$#2$#@$#@$'
            ],
            [
                false,
                '../../jpeg'
            ]
        ];
    }
}
