<?php
/**
 * Test for \Magento\Framework\Filesystem\Driver\File
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filesystem\Driver;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driver;

    /**
     * @var string
     */
    protected $absolutePath;

    /**
     * get relative path for test
     *
     * @param $relativePath
     * @return string
     */
    protected function getTestPath($relativePath)
    {
        return $this->absolutePath . $relativePath;
    }

    /**
     * Set up
     */
    public function setUp()
    {
        $this->driver = new \Magento\Framework\Filesystem\Driver\File();
        $this->absolutePath = dirname(__DIR__) . '/_files/';
    }

    /**
     * test read recursively read
     */
    public function testReadDirectoryRecursively()
    {
        $paths = [
            'foo/bar',
            'foo/bar/baz',
            'foo/bar/baz/file_one.txt',
            'foo/bar/file_two.txt',
            'foo/file_three.txt',
        ];
        $expected = array_map(['self', 'getTestPath'], $paths);
        $actual = $this->driver->readDirectoryRecursively($this->getTestPath('foo'));
        sort($actual);
        $this->assertEquals($expected, $actual);
    }

    /**
     * test exception
     *
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testReadDirectoryRecursivelyFailure()
    {
        $this->driver->readDirectoryRecursively($this->getTestPath('not-existing-directory'));
    }
}
