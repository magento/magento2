<?php
/**
 * Test for \Magento\Filesystem\Adapter\Local
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Adapter;

class LocalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Filesystem\Adapter\Local
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_adapter = new \Magento\Filesystem\Adapter\Local();

        \Magento\Io\File::rmdirRecursive(self::_getTmpDir());
        mkdir(self::_getTmpDir());
    }

    protected function tearDown()
    {
        \Magento\Io\File::rmdirRecursive(self::_getTmpDir());
    }

    protected static function _getTmpDir()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir')
            ->getDir(\Magento\App\Dir::VAR_DIR) . DIRECTORY_SEPARATOR . 'Magento\Filesystem\Adapter\LocalTest';
    }

    /**
     * @return string
     */
    protected static function _getFixturesPath()
    {
        return __DIR__ . '/../_files/';
    }

    /**
     * @param string $key
     * @param bool $expected
     * @dataProvider existsDataProvider
     */
    public function testExists($key, $expected)
    {
        $this->assertEquals($expected, $this->_adapter->exists($key));
    }

    /**
     * @return array
     */
    public static function existsDataProvider()
    {
        return array(
            'existed file' => array(self::_getFixturesPath() . 'popup.csv', true),
            'not existed file' => array(self::_getFixturesPath() . 'popup2.css', false),
        );
    }

    /**
     * @param string $fileName
     * @param string $expectedContent
     * @dataProvider readDataProvider
     */
    public function testRead($fileName, $expectedContent)
    {
        $this->assertEquals($expectedContent, $this->_adapter->read($fileName));
    }

    /**
     * @return array
     */
    public static function readDataProvider()
    {
        return array(
            'read' => array(self::_getFixturesPath() . 'popup.csv', 'var myData = 5;'),
        );
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to read contents of 'non-existing-file.txt'
     */
    public function testReadException()
    {
        $this->_adapter->read('non-existing-file.txt');
    }

    /**
     * @param string $fileName
     * @param string $fileData
     * @dataProvider writeDataProvider
     */
    public function testWrite($fileName, $fileData)
    {
        $this->_adapter->write($fileName, $fileData);
        $this->assertFileExists($fileName);
        $this->assertEquals(file_get_contents($fileName), $fileData);
    }

    /**
     * @return array
     */
    public static function writeDataProvider()
    {
        return array(
            'correct file' => array(self::_getTmpDir() . '/tempFile.css', 'temporary data'),
            'empty file' => array(self::_getTmpDir() . '/tempFile2.css', '')
        );
    }

    public function testWriteException()
    {
        $filename = __DIR__;
        $this->setExpectedException('Magento\Filesystem\FilesystemException',
            "Failed to write contents to '{$filename}'");
        $this->_adapter->write($filename, 'any contents');
    }

    /**
     * Test, that deleting non-existing file doesn't produce exceptions
     */
    public function testDeleteNotExists()
    {
        $fileName = self::_getTmpDir() . '/tempFile3.css';
        $this->_adapter->delete($fileName);
    }

    public function testDeleteDir()
    {
        $dirName = self::_getTmpDir() . '/new_directory';
        $fileName = $dirName . '/tempFile3.css';
        mkdir($dirName, 0755);
        file_put_contents($fileName, 'test data');
        $this->_adapter->delete($dirName);
        $this->assertFileNotExists($dirName);
        $this->assertFileNotExists($fileName);
    }

    public function testDelete()
    {
        $fileName = self::_getTmpDir() . '/tempFile3.css';
        file_put_contents($fileName, 'test data');
        $this->_adapter->delete($fileName);
        $this->assertFileNotExists($fileName);
    }

    public function testChangePermissionsFile()
    {
        $fileName = self::_getTmpDir() . '/tempFile3.css';
        file_put_contents($fileName, 'test data');
        $this->_adapter->changePermissions($fileName, 0666, false);
        $this->assertEquals(0666, fileperms($fileName) & 0777);
    }

    public function testChangePermissionsDir()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped("chmod may not work for Windows");
        }
        $dirName = self::_getTmpDir() . '/new_directory2';
        $fileName = $dirName . '/tempFile3.css';
        mkdir($dirName, 0777);
        file_put_contents($fileName, 'test data');
        $this->_adapter->changePermissions($dirName, 0755, true);
        $this->assertEquals(0755, fileperms($dirName) & 0777);
        $this->assertEquals(0755, fileperms($fileName) & 0777);
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to change mode of 'non-existing-file.txt'
     */
    public function testChangePermissionsException()
    {
        $this->_adapter->changePermissions('non-existing-file.txt', 0666, false);
    }

    public function testGetFileMd5()
    {
        $this->assertEquals('e5f30e10b8965645d5f8ed5999d88600',
            $this->_adapter->getFileMd5(self::_getFixturesPath() . 'popup.csv'));
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to get hash of 'non-existing-file.txt'
     */
    public function testGetFileMd5Exception()
    {
        $this->_adapter->getFileMd5('non-existing-file.txt');
    }

    public function testIsFile()
    {
        $this->assertTrue($this->_adapter->isFile(self::_getFixturesPath() . 'popup.csv'));
    }

    public function testIsWritable()
    {
        $this->assertTrue($this->_adapter->isWritable(self::_getFixturesPath() . 'popup.csv'));
    }

    public function testIsReadable()
    {
        $this->assertTrue($this->_adapter->isReadable(self::_getFixturesPath() . 'popup.csv'));
    }

    public function testCreateStream()
    {
        $stream = $this->_adapter->createStream(self::_getFixturesPath() . 'popup.csv');
        $this->assertInstanceOf('Magento\Filesystem\Stream\Local', $stream);
    }

    /**
     * @param string $sourceName
     * @param string $targetName
     * @throws \Exception
     * @dataProvider renameDataProvider
     */
    public function testRename($sourceName, $targetName)
    {
        file_put_contents($sourceName, 'test data');
        $this->_adapter->rename($sourceName, $targetName);
        $this->assertFileExists($targetName);
        $this->assertFileNotExists($sourceName);
        $this->assertEquals(file_get_contents($targetName), 'test data');
    }

    /**
     * @return array
     */
    public static function renameDataProvider()
    {
        return array(
            'test 1' => array(self::_getTmpDir() . '/file1.js', self::_getTmpDir() . '/file2.js'),
        );
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to rename 'non-existing-file.txt' to 'any-new-file.txt'
     */
    public function testRenameException()
    {
        $this->_adapter->rename('non-existing-file.txt', 'any-new-file.txt');
    }


    public function testIsDirectory()
    {
        $this->assertTrue($this->_adapter->isDirectory(self::_getFixturesPath()));
        $this->assertFalse($this->_adapter->isDirectory(self::_getFixturesPath() . 'popup.csv'));
    }

    public function testCreateDirectory()
    {
        $directoryName = self::_getTmpDir() . '/new_directory';
        $this->_adapter->createDirectory($directoryName, 0755);
        $this->assertFileExists($directoryName);
        $this->assertTrue(is_dir($directoryName));
    }

    public function testCreateDirectoryException()
    {
        $filename = __FILE__;
        $this->setExpectedException('Magento\Filesystem\FilesystemException', "Failed to create '{$filename}'");
        $this->_adapter->createDirectory($filename, 0755);
    }

    /**
     * @dataProvider touchDataProvider
     * @param string $fileName
     * @param bool $newFile
     */
    public function testTouch($fileName, $newFile = false)
    {
        if ($newFile) {
            $this->assertFileNotExists($fileName);
        } else {
            $this->assertFileExists($fileName);
        }
        $this->_adapter->touch($fileName);
        $this->assertFileExists($fileName);
    }

    /**
     * @return array
     */
    public static function touchDataProvider()
    {
        return array(
            'update file' => array(self::_getFixturesPath() . 'popup.csv', false),
            'create file' => array(self::_getTmpDir() . '/popup.css', true)
        );
    }

    public function testTouchException()
    {
        $filename = __FILE__ . '/invalid';
        $this->setExpectedException('Magento\Filesystem\FilesystemException', "Failed to touch '{$filename}'");
        $this->_adapter->touch($filename);
    }

    /**
     * @param string $sourceName
     * @param string $targetName
     * @dataProvider renameDataProvider
     */
    public function testCopy($sourceName, $targetName)
    {
        $testData = 'test data';
        file_put_contents($sourceName, $testData);
        $this->_adapter->copy($sourceName, $targetName);
        $this->assertFileExists($targetName);
        $this->assertFileExists($sourceName);
        $this->assertEquals($testData, file_get_contents($targetName));
        $this->assertEquals($testData, file_get_contents($targetName));
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to copy 'non-existing-file.txt' to 'any-new-file.txt'
     */
    public function testCopyException()
    {
        $this->_adapter->copy('non-existing-file.txt', 'any-new-file.txt');
    }

    public function testGetMTime()
    {
        $filePath = self::_getTmpDir() . '/mtime.txt';
        $this->_adapter->write($filePath, 'Test');
        $this->assertFileExists($filePath);
        $this->assertGreaterThan(0, $this->_adapter->getMTime($filePath));
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to get modification time of 'non-existing-file.txt'
     */
    public function testGetMTimeException()
    {
        $this->_adapter->getMTime('non-existing-file.txt');
    }

    /**
     * @param string $content
     * @param int $expectedSize
     * @dataProvider getFileSizeDataProvider
     */
    public function testGetFileSize($content, $expectedSize)
    {
        $filePath = self::_getTmpDir() . '/filesize.txt';
        $this->_adapter->write($filePath, $content);
        $this->assertFileExists($filePath);
        $this->assertEquals($expectedSize, $this->_adapter->getFileSize($filePath));
    }

    /**
     * @return array
     */
    public static function getFileSizeDataProvider()
    {
        return array(
            'usual file' => array('1234', 4),
            'empty file' => array('', 0),
        );
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Failed to get file size of 'non-existing-file.txt'
     */
    public function testGetFileSizeException()
    {
        $this->_adapter->getFileSize('non-existing-file.txt');
    }

    /**
     * @dataProvider getNestedKeysDataProvider
     * @param string $path
     * @param array $expectedKeys
     */
    public function testGetNestedKeys($path, $expectedKeys)
    {
        $actualKeys = $this->_adapter->getNestedKeys($path);
        sort($actualKeys);
        $this->assertEquals($expectedKeys, $actualKeys);
    }

    /**
     * @return array
     */
    public static function getNestedKeysDataProvider()
    {
        return array(
            array(
                self::_getFixturesPath() . 'foo',
                array(
                    self::_getFixturesPath() . 'foo/bar',
                    self::_getFixturesPath() . 'foo/bar/baz',
                    self::_getFixturesPath() . 'foo/bar/baz/file_one.txt',
                    self::_getFixturesPath() . 'foo/bar/file_two.txt',
                    self::_getFixturesPath() . 'foo/file_three.txt',
                )
            ),
            array(
                self::_getFixturesPath() . 'foo/bar/baz',
                array(self::_getFixturesPath() . 'foo/bar/baz/file_one.txt')
            )
        );
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage The directory '/unknown_directory' does not exist.
     */
    public function testGetNestedKeysInUnknownDirectory()
    {
        $this->_adapter->getNestedKeys('/unknown_directory');
    }

    /**
     * @dataProvider getNestedFilesDataProvider
     * @param string $pattern
     * @param array $expectedKeys
     */
    public function testSearchKeys($pattern, $expectedKeys)
    {
        $this->assertEquals($expectedKeys, $this->_adapter->searchKeys($pattern));
    }

    /**
     * @return array
     */
    public static function getNestedFilesDataProvider()
    {
        return array(
            array(
                self::_getFixturesPath() . 'foo/*',
                array(
                    self::_getFixturesPath() . 'foo/bar',
                    self::_getFixturesPath() . 'foo/file_three.txt',
                )
            ),
            array(
                self::_getFixturesPath() . 'foo/*/file_*',
                array(
                    self::_getFixturesPath() . 'foo/bar/file_two.txt',
                )
            )
        );
    }

    public function testSearchKeysException()
    {
        $pattern = str_repeat('1', 20000); // Overflow the glob() length limit (Win - 260b, Linux - 1k-8k)
        $this->setExpectedException('Magento\Filesystem\FilesystemException',
            "Failed to resolve the file pattern '{$pattern}'");
        $this->_adapter->searchKeys($pattern);
    }
}
