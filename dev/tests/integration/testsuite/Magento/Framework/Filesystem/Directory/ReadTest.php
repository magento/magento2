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

    public function testGetAbsolutePathOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->getAbsolutePath('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->getAbsolutePath('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->getAbsolutePath('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testGetRelativePathOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->getRelativePath(__DIR__ . '/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->getRelativePath(__DIR__ . '//./..////Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->getRelativePath(__DIR__ . '\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->getRelativePath('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(4, $exceptions);
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

    public function testReadOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->read('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->read('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->read('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testSearchOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->search('/*/*.txt', '../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->search('/*/*.txt', '//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->search('/*/*.txt', '\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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
        return [
            ['foo', 'bar', true],
            ['foo', 'bar/baz/', true],
            ['foo', 'bar/notexists', false],
            ['foo', 'foo/../bar/', true],
            ['foo', 'foo/../notexists/', false]
        ];
    }

    public function testIsExistOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->isExist('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isExist('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isExist('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testStatOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->stat('bar/../../../Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->stat('bar//./..///../../Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->stat('bar\..\..\..\Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testIsReadableOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->isReadable('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isReadable('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isReadable('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testIsFileOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->isFile('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isFile('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isFile('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testIsDirectoryOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->isDirectory('../../Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isDirectory('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->isDirectory('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testOpenFileOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->openFile('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->openFile('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->openFile('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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
                '"field1", "field2"' . PHP_EOL . '"field3", "field4"' . PHP_EOL
            ]
        ];
    }

    public function testReadFileOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->readFile('../../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->readFile('//./..///../Directory/ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->readFile('\..\..\Directory\ReadTest.php');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
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

    public function testReadRecursivelyOutside()
    {
        $exceptions = 0;
        $dir = $this->getDirectoryInstance('foo');
        try {
            $dir->readRecursively('../../Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->readRecursively('//./..///../Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        try {
            $dir->readRecursively('\..\..\Directory');
        } catch (ValidatorException $exception) {
            $exceptions++;
        }
        $this->assertEquals(3, $exceptions);
    }
}
