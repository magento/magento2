<?php
/**
 * Test for \Magento\Framework\Filesystem\Directory\Read
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\ValidatorException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ReadTest
 * Test for Magento\Framework\Filesystem\Directory\Read class
 */
class ReadTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testGetAbsolutePathOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->getAbsolutePath($path . '/ReadTest.php');
    }

    /**
     * @return array
     */
    public function pathDataProvider(): array
    {
        return [
            ['../../Directory'],
            ['//./..///../Directory'],
            ['\..\..\Directory'],
        ];
    }

    public function testGetRelativePath()
    {
        $dir = $this->getDirectoryInstance('foo');
        $this->assertEquals(
            'file_three.txt',
            $dir->getRelativePath('file_three.txt')
        );
        $this->assertEquals('', $dir->getRelativePath());
        $this->assertEquals('bar', $dir->getRelativePath(__DIR__ . '/../_files/foo/bar'));
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testGetRelativePathOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->getRelativePath(__DIR__ . $path . '/ReadTest.php');
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
        return [
            ['foo', null, ['bar', 'file_three.txt']],
            ['foo/bar', null, ['baz', 'file_two.txt']],
            ['foo', 'bar', ['bar/baz', 'bar/file_two.txt']]
        ];
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testReadOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->read($path . '/ReadTest.php');
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
        return [
            ['foo', 'bar/*', ['bar/file_two.txt', 'bar/baz']],
            ['foo', '/*/*.txt', ['bar/file_two.txt']],
            ['foo', '/notfound/', []]
        ];
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testSearchOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->search('/*/*.txt', $path . '/ReadTest.php');
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
        return [['foo', 'bar', true], ['foo', 'bar/baz/', true], ['foo', 'bar/notexists', false]];
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testIsExistOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->isExist($path . '/ReadTest.php');
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
        $expectedInfo = [
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
            'blocks',
        ];
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
        return [['foo', 'bar'], ['foo', 'file_three.txt']];
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testStatOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->stat('bar/' . $path);
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
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testIsReadableOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->isReadable($path . '/ReadTest.php');
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
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testIsFileOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->isFile($path . '/ReadTest.php');
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
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testIsDirectoryOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->isDirectory($path . '/ReadTest.php');
    }

    /**
     * Data provider for testIsReadable
     *
     * @return array
     */
    public function isReadableProvider()
    {
        return [['foo', 'bar', true], ['foo', 'file_three.txt', true]];
    }

    /**
     * Data provider for testIsFile
     *
     * @return array
     */
    public function isFileProvider()
    {
        return [['bar', false], ['file_three.txt', true]];
    }

    /**
     * Data provider for testIsDirectory
     *
     * @return array
     */
    public function isDirectoryProvider()
    {
        return [['bar', true], ['file_three.txt', false]];
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
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testOpenFileOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->openFile($path . '/ReadTest.php');
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
        return [
            ['popup.csv', 'var myData = 5;'],
            [
                'data.csv',
                '"field1", "field2"' . PHP_EOL . '"field3", "field4"' . PHP_EOL,
            ],
        ];
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testReadFileOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->readFile($path . '/ReadTest.php');
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
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem\Directory\ReadFactory $directoryFactory */
        $directoryFactory = $objectManager->create(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        return $directoryFactory->create($fullPath);
    }

    /**
     * test read recursively read
     */
    public function testReadRecursively()
    {
        $expected = ['bar/baz/file_one.txt', 'bar', 'bar/baz', 'bar/file_two.txt', 'file_three.txt'];

        $dir = $this->getDirectoryInstance('foo');
        $actual = $dir->readRecursively('');
        $this->assertNotEquals($expected, $actual);
        sort($expected);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @param string $path
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @dataProvider pathDataProvider
     */
    public function testReadRecursivelyOutside(string $path)
    {
        $dir = $this->getDirectoryInstance('foo');

        $dir->readRecursively($path);
    }
}
