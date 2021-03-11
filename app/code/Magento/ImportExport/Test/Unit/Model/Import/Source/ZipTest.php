<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

class ZipTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $directory;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\Zip|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $zip;

    protected function setUp(): void
    {
        $this->directory = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Write::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelativePath'])
            ->getMock();
    }

    /**
     * Test destination argument for the second getRelativePath after preg_replace.
     *
     * @depends testConstructorInternalCalls
     * @dataProvider constructorFileDestinationMatchDataProvider
     */
    public function testConstructorFileDestinationMatch($fileName, $expectedfileName)
    {
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
                \Magento\ImportExport\Model\Import\Source\Zip::class
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
                \Magento\ImportExport\Model\Import\Source\Zip::class
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
        } catch (\PHPUnit\Framework\Error $e) {
            // Suppress any errors due to no control of Zip object dependency instantiation.
        }
    }
}
