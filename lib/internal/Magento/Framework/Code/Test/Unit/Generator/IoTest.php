<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Generator;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;

class IoTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const GENERATION_DIRECTORY = 'generation_directory';

    const CLASS_NAME = 'class_name';

    const CLASS_FILE_NAME = 'class/file/name';

    const FILE_CONTENT = "content";

    /**#@-*/

    /**
     * Basic code generation directory
     *
     * @var string
     */
    protected $_generationDirectory;

    /** @var \Magento\Framework\Code\Generator\Io */
    protected $_object;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $_filesystemDriverMock;

    /** @var string */
    protected $existingFile = '/Magento/Class/Exists.php';

    /** @var string */
    protected $nonExistingFile = '/Magento/Class/Does/Not/Exists.php';

    protected function setUp()
    {
        $this->_generationDirectory = rtrim(self::GENERATION_DIRECTORY, '/') . '/';

        $this->_filesystemDriverMock = $this->getMock('Magento\Framework\Filesystem\Driver\File');

        $this->_object = new \Magento\Framework\Code\Generator\Io(
            $this->_filesystemDriverMock,
            self::GENERATION_DIRECTORY
        );
    }

    protected function tearDown()
    {
        unset($this->_generationDirectory);
        unset($this->_filesystemMock);
        unset($this->_object);
        unset($this->_filesystemDriverMock);
    }

    public function testGetResultFileDirectory()
    {
        $expectedDirectory = self::GENERATION_DIRECTORY . '/' . 'class/';
        $this->assertEquals($expectedDirectory, $this->_object->getResultFileDirectory(self::CLASS_NAME));
    }

    public function testGetResultFileName()
    {
        $expectedFileName = self::GENERATION_DIRECTORY . '/class/name.php';
        $this->assertEquals($expectedFileName, $this->_object->generateResultFileName(self::CLASS_NAME));
    }

    /**
     * @dataProvider testWriteResultFileAlreadyExistsDataProvider
     */
    public function testWriteResultFileAlreadyExists($resultFileName, $fileExists, $exceptionDuringRename, $success)
    {
        $this->_filesystemDriverMock->expects($this->once())
            ->method('filePutContents')
            ->with(
                $this->stringContains($resultFileName),
                "<?php\n" . self::FILE_CONTENT
            )->willReturn(true);
        $isExistsInvocationCount = $exceptionDuringRename ? 1 : 0;
        $this->_filesystemDriverMock->expects($this->exactly($isExistsInvocationCount))
            ->method('isExists')
            ->willReturn($fileExists);

        if (!$exceptionDuringRename) {
            $renameMockEvent = $this->returnValue(true);
        } else if ($fileExists) {
            $renameMockEvent = $this->throwException(new FileSystemException(new Phrase('File already exists')));
        } else {
            $exceptionMessage = 'Some error renaming file';
            $renameMockEvent = $this->throwException(new FileSystemException(new Phrase($exceptionMessage)));
            $this->setExpectedException('\Magento\Framework\Exception\FileSystemException', $exceptionMessage);
        }

        $this->_filesystemDriverMock->expects($this->once())
            ->method('rename')
            ->with(
                $this->stringContains($resultFileName),
                $resultFileName
            )->will($renameMockEvent); //Throw exception or return true

        $this->assertSame($success, $this->_object->writeResultFile($resultFileName, self::FILE_CONTENT));
    }

    public function testWriteResultFileAlreadyExistsDataProvider()
    {
        return [
            'Writing file succeeds: writeResultFile succeeds' => [
                'resultFileName' => $this->nonExistingFile,
                'fileExists' => false,
                'exceptionDuringRename' => false,
                'success' => true

            ],
            'Writing file fails because class already exists on disc: writeResultFile succeeds' => [
                'resultFileName' => $this->existingFile,
                'fileExists' => true,
                'exceptionDuringRename' => true,
                'success' => true
            ],
            'Error renaming file, btu class does not exist on disc: writeResultFile throws exception and fails' => [
                'resultFileName' => $this->nonExistingFile,
                'fileExists' => false,
                'exceptionDuringRename' => true,
                'success' => false
            ]
        ];
    }

    public function testMakeGenerationDirectoryWritable()
    {
        $this->_filesystemDriverMock->expects(
            $this->once()
        )->method(
            'isWritable'
        )->with(
            $this->equalTo($this->_generationDirectory)
        )->will(
            $this->returnValue(true)
        );

        $this->assertTrue($this->_object->makeGenerationDirectory());
    }

    public function testMakeGenerationDirectoryReadOnly()
    {
        $this->_filesystemDriverMock->expects(
            $this->once()
        )->method(
            'isWritable'
        )->with(
            $this->equalTo($this->_generationDirectory)
        )->will(
            $this->returnValue(false)
        );

        $this->_filesystemDriverMock->expects(
            $this->once()
        )->method(
            'createDirectory'
        )->with(
            $this->equalTo($this->_generationDirectory),
            $this->anything()
        )->will(
            $this->returnValue(true)
        );

        $this->assertTrue($this->_object->makeGenerationDirectory());
    }

    public function testGetGenerationDirectory()
    {
        $this->assertEquals($this->_generationDirectory, $this->_object->getGenerationDirectory());
    }

    /**
     * @dataProvider fileExistsDataProvider
     * @param $fileName
     * @param $exists
     */
    public function testFileExists($fileName, $exists)
    {
        $this->_filesystemDriverMock->expects(
            $this->once()
        )->method(
            'isExists'
        )->with(
            $this->equalTo($fileName)
        )->will(
            $this->returnValue($exists)
        );

        $this->assertSame($exists, $this->_object->fileExists($fileName));
    }

    public function fileExistsDataProvider()
    {
        return [
            ['fileName' => $this->existingFile, 'exists' => true],
            ['fileName' => $this->nonExistingFile, 'exists' => false]
        ];
    }
}
