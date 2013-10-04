<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Code
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Code\Generator;

class IoTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const DIRECTORY_SEPARATOR  = '|';
    const GENERATION_DIRECTORY = 'generation_directory';
    const CLASS_NAME           = 'class_name';
    const CLASS_FILE_NAME      = 'class|file|name';
    const FILE_NAME            = 'test_file';
    const FILE_CONTENT         = "content";
    /**#@-*/

    /**
     * Basic code generation directory
     *
     * @var string
     */
    protected $_generationDirectory;

    /**
     * @var \Magento\Code\Generator\Io
     */
    protected $_object;

    /**
     * @var \Magento\Io\File|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ioObjectMock;

    /**
     * @var \Magento\Autoload\IncludePath|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_autoLoaderMock;

    protected function setUp()
    {
        $this->_generationDirectory
            = rtrim(self::GENERATION_DIRECTORY, self::DIRECTORY_SEPARATOR) . self::DIRECTORY_SEPARATOR;

        $this->_ioObjectMock = $this->getMock('Magento\Io\File',
            array('dirsep', 'isWriteable', 'mkdir', 'fileExists', 'write')
        );
        $this->_ioObjectMock->expects($this->any())
            ->method('dirsep')
            ->will($this->returnValue(self::DIRECTORY_SEPARATOR));

        $this->_autoLoaderMock = $this->getMock(
            'Magento\Autoload\IncludePath', array('getFilePath'), array(), '', false
          );
        $this->_autoLoaderMock->staticExpects($this->any())
            ->method('getFilePath')
            ->with(self::CLASS_NAME)
            ->will($this->returnValue(self::CLASS_FILE_NAME));

        $this->_object = new \Magento\Code\Generator\Io($this->_ioObjectMock, $this->_autoLoaderMock,
            self::GENERATION_DIRECTORY
        );
    }

    protected function tearDown()
    {
        unset($this->_generationDirectory);
        unset($this->_ioObjectMock);
        unset($this->_autoLoaderMock);
        unset($this->_object);
    }

    public function testGetResultFileDirectory()
    {
        $expectedDirectory = self::GENERATION_DIRECTORY . self::DIRECTORY_SEPARATOR . 'class|file|';
        $this->assertEquals($expectedDirectory, $this->_object->getResultFileDirectory(self::CLASS_NAME));
    }

    public function testGetResultFileName()
    {
        $expectedFileName = self::GENERATION_DIRECTORY . self::DIRECTORY_SEPARATOR . self::CLASS_FILE_NAME;
        $this->assertEquals($expectedFileName, $this->_object->getResultFileName(self::CLASS_NAME));
    }

    public function testWriteResultFile()
    {
        $this->_ioObjectMock->expects($this->once())
            ->method('write')
            ->with($this->equalTo(self::FILE_NAME), $this->equalTo("<?php\n" . self::FILE_CONTENT))
            ->will($this->returnValue(true));

        $this->assertTrue($this->_object->writeResultFile(self::FILE_NAME, self::FILE_CONTENT));
    }

    public function testMakeGenerationDirectoryWritable()
    {
        $this->_ioObjectMock->expects($this->once())
            ->method('isWriteable')
            ->with($this->equalTo($this->_generationDirectory))
            ->will($this->returnValue(true));

        $this->assertTrue($this->_object->makeGenerationDirectory());
    }

    public function testMakeGenerationDirectoryReadOnly()
    {
        $this->_ioObjectMock->expects($this->once())
            ->method('isWriteable')
            ->with($this->equalTo($this->_generationDirectory))
            ->will($this->returnValue(false));

        $this->_ioObjectMock->expects($this->once())
            ->method('mkdir')
            ->with($this->equalTo($this->_generationDirectory), $this->anything(), $this->isTrue())
            ->will($this->returnValue(true));

        $this->assertTrue($this->_object->makeGenerationDirectory());
    }

    public function testGetGenerationDirectory()
    {
        $this->assertEquals($this->_generationDirectory, $this->_object->getGenerationDirectory());
    }

    public function testFileExists()
    {
        $this->_ioObjectMock->expects($this->once())
            ->method('fileExists')
            ->with($this->equalTo(self::FILE_NAME), $this->isTrue())
            ->will($this->returnValue(false));

        $this->assertFalse($this->_object->fileExists(self::FILE_NAME));
    }
}
