<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

use Magento\Framework\Filesystem\Directory\Write;
use Magento\ImportExport\Model\Import\Source\Zip;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ZipTest extends TestCase
{
    /**
     * @var Write|MockObject
     */
    private $directory;

    /**
     * @var Zip|MockObject
     */
    private $zip;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->directory = $this->getMockBuilder(Write::class)->disableOriginalConstructor()
            ->onlyMethods(['getRelativePath'])
            ->getMock();
    }

    /**
     * Test destination argument for the second getRelativePath after preg_replace.
     *
     * @return void
     * @dataProvider constructorFileDestinationMatchDataProvider
     */
    public function testConstructorFileDestinationMatch($fileName, $expectedfileName): void
    {
        $this->markTestIncomplete('The implementation of constructor has changed. Rewrite test to cover changes.');

        $this->directory->method('getRelativePath')
            ->withConsecutive([$fileName], [$expectedfileName]);
        $this->invokeConstructor($fileName);
    }

    /**
     * @return array
     */
    public function constructorFileDestinationMatchDataProvider(): array
    {
        return [
            [
                '$fileName' => 'test_file.txt',
                '$expectedfileName' => 'test_file.txt'
            ],
            [
                '$fileName' => 'test_file.zip',
                '$expectedfileName' => 'test_file.csv'
            ],
            [
                '$fileName' => '.ziptest_.zip.file.zip.ZIP',
                '$expectedfileName' => '.ziptest_.zip.file.zip.csv'
            ]
        ];
    }

    /**
     * Instantiate zip mock and invoke its constructor.
     *
     * @return void
     * @param string $fileName
     */
    private function invokeConstructor($fileName): void
    {
        try {
            $this->zip = $this->getMockBuilder(
                Zip::class
            )
                ->setConstructorArgs(
                    [
                        $fileName,
                        $this->directory,
                        [],
                    ]
                )
                ->getMock();

            $reflectedClass = new \ReflectionClass(
                Zip::class
            );
            $constructor = $reflectedClass->getConstructor();
            $constructor->invoke(
                $this->zip,
                [
                    $fileName,
                    $this->directory,
                    [],
                ]
            );
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
