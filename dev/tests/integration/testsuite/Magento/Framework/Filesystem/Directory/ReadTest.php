<?php
/**
 * Test for \Magento\Framework\Filesystem\Directory\Read
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ReadTest
 * Test for Magento\Framework\Filesystem\Directory\Read class
 */
class ReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test instance of Read
     */
    public function testInstance()
    {
        $dir = $this->getDirectoryInstance('foo');
        $this->assertTrue($dir instanceof ReadInterface);
    }

    /**
     * Test for getAbsolutePath method
     */
    public function testGetAbsolutePath()
    {
        $dir = $this->getDirectoryInstance('foo');
        $this->assertContains('_files/foo', $dir->getAbsolutePath());
        $this->assertContains('_files/foo/bar', $dir->getAbsolutePath('bar'));
    }

    public function testGetRelativePath()
    {
        $dir = $this->getDirectoryInstance('foo');
        $this->assertEquals('', $dir->getRelativePath());
        $this->assertEquals('bar', $dir->getRelativePath(__DIR__ . '/../_files/foo/bar'));
    }

    /**
     * Test for read method
     *
     * @dataProvider readProvider
     * @param string $dirPath
     * @param string $readPath
     * @param array $expectedResult
     */
    public function testRead($dirPath, $readPath, $expectedResult)
    {
        $dir = $this->getDirectoryInstance($dirPath);
        $result = $dir->read($readPath);
        foreach ($expectedResult as $path) {
            $this->assertTrue(in_array($path, $result));
        }
    }

    /**
     * Data provider for testRead
     *
     * @return array
     */
    public function readProvider()
    {
        return array(
            array('foo', null, array('bar', 'file_three.txt')),
            array('foo/bar', null, array('baz', 'file_two.txt')),
            array('foo', 'bar', array('bar/baz', 'bar/file_two.txt'))
        );
    }

    /**
     * Test for search method
     *
     * @dataProvider searchProvider
     * @param string $dirPath
     * @param string $pattern
     * @param array $expectedResult
     */
    public function testSearch($dirPath, $pattern, $expectedResult)
    {
        $dir = $this->getDirectoryInstance($dirPath);
        $result = $dir->search($pattern);
        foreach ($expectedResult as $path) {
            $this->assertTrue(in_array($path, $result));
        }
    }

    /**
     * Data provider for testSearch
     *
     * @return array
     */
    public function searchProvider()
    {
        return array(
            array('foo', 'bar/*', array('bar/file_two.txt', 'bar/baz')),
            array('foo', '/*/*.txt', array('bar/file_two.txt')),
            array('foo', '/notfound/', array())
        );
    }

    /**
     * Test for isExist method
     *
     * @dataProvider existsProvider
     * @param string $dirPath
     * @param string $path
     * @param bool $exists
     */
    public function testIsExist($dirPath, $path, $exists)
    {
        $dir = $this->getDirectoryInstance($dirPath);
        $this->assertEquals($exists, $dir->isExist($path));
    }

    /**
     * Data provider for testIsExist
     *
     * @return array
     */
    public function existsProvider()
    {
        return array(array('foo', 'bar', true), array('foo', 'bar/baz/', true), array('foo', 'bar/notexists', false));
    }

    /**
     * Test for stat method
     *
     * @dataProvider statProvider
     * @param string $dirPath
     * @param string $path
     */
    public function testStat($dirPath, $path)
    {
        $dir = $this->getDirectoryInstance($dirPath);
        $expectedInfo = array(
            'dev',
            'ino',
            'mode',
            'nlink',
            'uid',
            'gid',
            'rdev',
            'size',
            'atime',
            'mtime',
            'ctime',
            'blksize',
            'blocks'
        );
        $result = $dir->stat($path);
        foreach ($expectedInfo as $key) {
            $this->assertTrue(array_key_exists($key, $result));
        }
    }

    /**
     * Data provider for testStat
     *
     * @return array
     */
    public function statProvider()
    {
        return array(array('foo', 'bar'), array('foo', 'file_three.txt'));
    }

    /**
     * Test for isReadable method
     *
     * @dataProvider isReadableProvider
     * @param string $dirPath
     * @param string $path
     * @param bool $readable
     */
    public function testIsReadable($dirPath, $path, $readable)
    {
        $dir = $this->getDirectoryInstance($dirPath);
        $this->assertEquals($readable, $dir->isReadable($path));
    }

    /**
     * Test for isFile method
     *
     * @dataProvider isFileProvider
     * @param string $path
     * @param bool $isFile
     */
    public function testIsFile($path, $isFile)
    {
        $this->assertEquals($isFile, $this->getDirectoryInstance('foo')->isFile($path));
    }

    /**
     * Test for isDirectory method
     *
     * @dataProvider isDirectoryProvider
     * @param string $path
     * @param bool $isDirectory
     */
    public function testIsDirectory($path, $isDirectory)
    {
        $this->assertEquals($isDirectory, $this->getDirectoryInstance('foo')->isDirectory($path));
    }

    /**
     * Data provider for testIsReadable
     *
     * @return array
     */
    public function isReadableProvider()
    {
        return array(array('foo', 'bar', true), array('foo', 'file_three.txt', true));
    }

    /**
     * Data provider for testIsFile
     *
     * @return array
     */
    public function isFileProvider()
    {
        return array(array('bar', false), array('file_three.txt', true));
    }

    /**
     * Data provider for testIsDirectory
     *
     * @return array
     */
    public function isDirectoryProvider()
    {
        return array(array('bar', true), array('file_three.txt', false));
    }

    /**
     * Test for openFile method
     */
    public function testOpenFile()
    {
        $file = $this->getDirectoryInstance('foo')->openFile('file_three.txt');
        $file->close();
        $this->assertTrue($file instanceof \Magento\Framework\Filesystem\File\ReadInterface);
    }

    /**
     * Test readFile
     *
     * @dataProvider readFileProvider
     * @param string $path
     * @param string $content
     */
    public function testReadFile($path, $content)
    {
        $directory = $this->getDirectoryInstance('');
        $this->assertEquals($content, $directory->readFile($path));
    }

    /**
     * Data provider for testReadFile
     *
     * @return array
     */
    public function readFileProvider()
    {
        return array(
            array('popup.csv', 'var myData = 5;'),
            array('data.csv', '"field1", "field2"' . PHP_EOL . '"field3", "field4"' . PHP_EOL)
        );
    }

    /**
     * Get readable file instance
     * Get full path for files located in _files directory
     *
     * @param string $path
     * @return Read
     */
    private function getDirectoryInstance($path)
    {
        $fullPath = __DIR__ . '/../_files/' . $path;
        $config = array('path' => $fullPath);
        $objectManager = Bootstrap::getObjectManager();
        $directoryFactory = $objectManager->create('Magento\Framework\Filesystem\Directory\ReadFactory');
        return $directoryFactory->create(
            $config,
            new \Magento\Framework\Filesystem\DriverFactory(
                $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList')
            )
        );
    }

    /**
     * test read recursively read
     */
    public function testReadRecursively()
    {
        $expected = array('bar/baz/file_one.txt', 'bar', 'bar/baz', 'bar/file_two.txt', 'file_three.txt');

        $dir = $this->getDirectoryInstance('foo');
        $actual = $dir->readRecursively('');
        $this->assertNotEquals($expected, $actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }
}
