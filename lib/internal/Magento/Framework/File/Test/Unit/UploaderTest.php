<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File\Test\Unit;

/**
 * Unit Test class for \Magento\Framework\File\Uploader.
 */
class UploaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $fileName
     * @param string $expectedCorrectedFileName
     *
     * @return void
     * @dataProvider getCorrectFileNameProvider
     */
    public function testGetCorrectFileName(string $fileName, string $expectedCorrectedFileName): void
    {
        $this->assertEquals(
            $expectedCorrectedFileName,
            \Magento\Framework\File\Uploader::getCorrectFileName($fileName)
        );
    }

    /**
     * @return array
     */
    public function getCorrectFileNameProvider(): array
    {
        return [
            [
                '^&*&^&*^$$$$()',
                'file.',
            ],
            [
                '^&*&^&*^$$$$().png',
                'file.png',
            ],
            [
                '_',
                'file.',
            ],
            [
                '_.jpg',
                'file.jpg',
            ],
            [
                'a.' . str_repeat('b', 88),
                'a.' . str_repeat('b', 88),
            ],
        ];
    }

    /**
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filename is too long; must be 90 characters or less
     */
    public function testGetCorrectFileNameWithOverLimitInputNameLength(): void
    {
        \Magento\Framework\File\Uploader::getCorrectFileName('a.' . str_repeat('b', 89));
    }
}
