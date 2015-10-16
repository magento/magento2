<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    const FILE_NAME = 'test_file';

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
    protected $existingResultClassFile;

    protected function setUp()
    {
        $this->_generationDirectory = rtrim(self::GENERATION_DIRECTORY, '/') . '/';

        $this->_filesystemDriverMock = $this->getMock('Magento\Framework\Filesystem\Driver\File');

        $this->_object = new \Magento\Framework\Code\Generator\Io(
            $this->_filesystemDriverMock,
            self::GENERATION_DIRECTORY
        );
        $this->existingResultClassFile =
            (new ComponentRegistrar())->getPath(ComponentRegistrar::LIBRARY, 'magento/framework')
            . '/Code/Test/Unit/Generator/TestAsset/ExistingResultClass.php';
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
        $this->assertEquals($expectedFileName, $this->_object->getResultFileName(self::CLASS_NAME));
    }

    public function testWriteResultFile()
    {
        $this->_filesystemDriverMock->expects($this->once())
            ->method('filePutContents')
            ->with(
                $this->stringContains(self::FILE_NAME),
                "<?php\n" . self::FILE_CONTENT
            )->willReturn(true);

        $this->_filesystemDriverMock->expects($this->once())
            ->method('rename')
            ->with(
                $this->stringContains(self::FILE_NAME),
                self::FILE_NAME
            )->willReturn(true);

        $this->assertTrue($this->_object->writeResultFile(self::FILE_NAME, self::FILE_CONTENT));
    }

    public function testWriteResultFileAlreadyExists()
    {
        $this->_filesystemDriverMock->expects($this->once())
            ->method('filePutContents')
            ->with(
                $this->stringContains($this->existingResultClassFile),
                "<?php\n" . self::FILE_CONTENT
            )->willReturn(true);

        $this->_filesystemDriverMock->expects($this->once())
            ->method('rename')
            ->with(
                $this->stringContains($this->existingResultClassFile),
                $this->existingResultClassFile
            )->willThrowException(new FileSystemException(new Phrase('File already exists')));

        $this->assertTrue($this->_object->writeResultFile($this->existingResultClassFile, self::FILE_CONTENT));
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

    public function testFileExists()
    {
        $this->_filesystemDriverMock->expects(
            $this->once()
        )->method(
            'isExists'
        )->with(
            $this->equalTo(self::FILE_NAME)
        )->will(
            $this->returnValue(false)
        );

        $this->assertFalse($this->_object->fileExists(self::FILE_NAME));
    }
}
