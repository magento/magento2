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
    protected $directory;

    /**
     * @var Zip|MockObject
     */
    protected $zip;

    protected function setUp(): void
    {
        $this->directory = $this->getMockBuilder(Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelativePath'])
            ->getMock();
    }

    /**
     * Test destination argument for the second getRelativePath after preg_replace.
     *
     * @dataProvider constructorFileDestinationMatchDataProvider
     */
    public function testConstructorFileDestinationMatch($fileName, $expectedfileName)
    {
        $this->markTestIncomplete('The implementation of constructor has changed. Rewrite test to cover changes.');

        $this->directory->expects($this->at(0))->method('getRelativePath')->with($fileName);
        $this->directory->expects($this->at(1))->method('getRelativePath')->with($expectedfileName);
        $this->_invokeConstructor($fileName);
    }

    /**
     * @return array
     */
    public function constructorFileDestinationMatchDataProvider()
    {
        return [
            [
                '$fileName' => 'test_file.txt',
                '$expectedfileName' => 'test_file.txt',
            ],
            [
                '$fileName' => 'test_file.zip',
                '$expectedfileName' => 'test_file.csv',
            ],
            [
                '$fileName' => '.ziptest_.zip.file.zip.ZIP',
                '$expectedfileName' => '.ziptest_.zip.file.zip.csv',
            ]
        ];
    }

    /**
     * Instantiate zip mock and invoke its constructor.
     *
     * @param string $fileName
     */
    protected function _invokeConstructor($fileName)
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
