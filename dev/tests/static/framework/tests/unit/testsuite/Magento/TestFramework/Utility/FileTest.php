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

    public function testGetPhpFiles()
    {
        $appFiles = [
            'file1',
            'file2'
        ];
        $setupFiles = [
            'file3'
        ];
        $expected = [
            'file1' => ['file1'],
            'file2' => ['file2'],
            'file3' => ['file3']
        ];
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
                Files::INCLUDE_APP_CODE
                | Files::INCLUDE_PUB_CODE
                | Files::INCLUDE_LIBS
                | Files::INCLUDE_TEMPLATES
                | Files::INCLUDE_TESTS
                | Files::INCLUDE_NON_CLASSES
            )
            ->willReturn($appFiles);
        $this->assertEquals($expected, $this->file->getPhpFiles());
    }
}
