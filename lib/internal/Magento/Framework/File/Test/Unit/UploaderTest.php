<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

use Magento\Framework\File\Uploader;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test class for \Magento\Framework\File\Uploader
 */
class UploaderTest extends TestCase
{
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
                'a.' . str_repeat('b', 89),
                true
            ]
        ];
    }
}
