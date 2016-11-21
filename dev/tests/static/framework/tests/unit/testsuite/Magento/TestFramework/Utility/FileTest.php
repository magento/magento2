<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Utility\File\RegexIteratorFactory;
use Magento\TestFramework\Utility\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Files|PHPUnit_Framework_MockObject_MockObject
     */
    private $fileUtilitiesMock;

    /**
     * @var RegexIteratorFactory|PHPUnit_Framework_MockObject_MockObject
     */
    private $regexIteratorFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var File
     */
    private $file;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->fileUtilitiesMock = $this->getMock(Files::class, [], [], '', false);
        $this->regexIteratorFactoryMock = $this->getMock(RegexIteratorFactory::class, [], [], '', false);
        $this->file = $this->objectManager->getObject(
            File::class,
            [
                'fileUtilities' => $this->fileUtilitiesMock,
                'regexIteratorFactory' => $this->regexIteratorFactoryMock
            ]
        );
    }

    public function testGetPhpFilesWithoutSetup()
    {
        $appFiles = [
            'file1',
            'file2'
        ];
        $expected = [
            'file1' => ['file1'],
            'file2' => ['file2']
        ];
        $this->regexIteratorFactoryMock->expects($this->never())
            ->method('create');
        $this->fileUtilitiesMock->expects($this->once())
            ->method('getPhpFiles')
            ->with(
                File::INCLUDE_APP_CODE
                | File::INCLUDE_PUB_CODE
                | File::INCLUDE_LIBS
                | File::INCLUDE_TEMPLATES
                | File::INCLUDE_TESTS
                | File::INCLUDE_NON_CLASSES
            )
            ->willReturn($appFiles);
        $actual = $this->file->getPhpFiles(
            File::INCLUDE_APP_CODE
            | File::INCLUDE_PUB_CODE
            | File::INCLUDE_LIBS
            | File::INCLUDE_TEMPLATES
            | File::INCLUDE_TESTS
            | File::INCLUDE_NON_CLASSES
            | File::AS_DATA_SET
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param array $appFiles
     * @param array$setupFiles
     * @param int $flags
     * @param array $expected
     * @dataProvider getPhpFilesWithSetupDataProvider
     */
    public function testGetPhpFilesWithSetup(
        $appFiles,
        $setupFiles,
        $flags,
        $expected
    ) {
        $iteratorMock = $this->getMock(\IteratorAggregate::class, [], [], '', false);
        $iteratorMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($setupFiles));
        $this->regexIteratorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($iteratorMock);
        $this->fileUtilitiesMock->expects($this->once())
            ->method('getPhpFiles')
            ->with(
                File::INCLUDE_APP_CODE
                | File::INCLUDE_PUB_CODE
                | File::INCLUDE_LIBS
                | File::INCLUDE_TEMPLATES
                | File::INCLUDE_TESTS
                | File::INCLUDE_SETUP
                | File::INCLUDE_NON_CLASSES
            )
            ->willReturn($appFiles);
        $this->assertEquals($expected, $this->file->getPhpFiles($flags));
    }

    /**
     * @return array
     */
    public function getPhpFilesWithSetupDataProvider()
    {
        $flags = File::INCLUDE_APP_CODE
            | File::INCLUDE_PUB_CODE
            | File::INCLUDE_LIBS
            | File::INCLUDE_TEMPLATES
            | File::INCLUDE_TESTS
            | File::INCLUDE_SETUP
            | File::INCLUDE_NON_CLASSES;
        return [
            [
                [
                    'file1',
                    'file2'
                ],
                [
                    'file3'
                ],
                $flags | File::AS_DATA_SET,
                [
                    'file1' => ['file1'],
                    'file2' => ['file2'],
                    'file3' => ['file3']
                ]
            ],
            [
                [
                    'file1',
                    'file2'
                ],
                [
                    'file3'
                ],
                $flags,
                [
                    'file1',
                    'file2',
                    'file3'
                ]
            ]
        ];
    }
}
