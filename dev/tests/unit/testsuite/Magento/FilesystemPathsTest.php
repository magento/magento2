<?php
/**
 * Unit Test for Magento_Filesystem
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
class Magento_FilesystemPathsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider absolutePathDataProvider
     * @param string $path
     * @param string $expected
     */
    public function testGetAbsolutePath($path, $expected)
    {
        $this->assertEquals($expected, Magento_Filesystem::getAbsolutePath($path));
    }

    /**
     * @return array
     */
    public function absolutePathDataProvider()
    {
        return array(
            array('/tmp/../file.txt', '/file.txt'),
            array('/tmp/../etc/mysql/file.txt', '/etc/mysql/file.txt'),
            array('/tmp/../file.txt', '/file.txt'),
            array('/tmp/./file.txt', '/tmp/file.txt'),
            array('/tmp/./../file.txt', '/file.txt'),
            array('/tmp/../../../file.txt', '/file.txt'),
            array('../file.txt', '/file.txt'),
            array('/../file.txt', '/file.txt'),
            array('/tmp/path/file.txt', '/tmp/path/file.txt'),
            array('/tmp/path', '/tmp/path'),
            array('C:\\Windows', 'C:/Windows'),
            array('C:\\Windows\\system32\\..', 'C:/Windows'),
        );
    }

    /**
     * @dataProvider pathDataProvider
     * @param array $parts
     * @param string $expected
     * @param bool $isAbsolute
     */
    public function testGetPathFromArray(array $parts, $expected, $isAbsolute)
    {
        $expected = Magento_Filesystem::fixSeparator($expected);
        $this->assertEquals($expected, Magento_Filesystem::getPathFromArray($parts, $isAbsolute));
    }

    /**
     * @return array
     */
    public function pathDataProvider()
    {
        return array(
            array(array('etc', 'mysql', 'my.cnf'), '/etc/mysql/my.cnf',true),
            array(array('etc', 'mysql', 'my.cnf'), 'etc/mysql/my.cnf', false),
            array(array('C:', 'Windows', 'my.cnf'), 'C:/Windows/my.cnf', false),
            array(array('C:', 'Windows', 'my.cnf'), 'C:/Windows/my.cnf', true),
            array(array('C:', 'Windows', 'my.cnf'), 'C:\\Windows/my.cnf', true),
        );
    }

    /**
     * @dataProvider pathDataProvider
     * @param array $expected
     * @param string $path
     */
    public function testGetPathAsArray(array $expected, $path)
    {
        $this->assertEquals($expected, Magento_Filesystem::getPathAsArray($path));
    }

    /**
     * @dataProvider isAbsolutePathDataProvider
     * @param bool $isReal
     * @param string $path
     */
    public function testIsAbsolutePath($isReal, $path)
    {
        $this->assertEquals($isReal, Magento_Filesystem::isAbsolutePath($path));
    }

    /**
     * @return array
     */
    public function isAbsolutePathDataProvider()
    {
        return array(
            array(true, '/tmp/file.txt'),
            array(false, '/tmp/../etc/mysql/my.cnf'),
            array(false, '/tmp/../tmp/file.txt'),
            array(false, 'C:\Temp\..\tmpfile.txt'),
            array(true, 'C:\Temp\tmpfile.txt'),
            array(true, '/tmp/'),
            array(true, '/tmp'),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Path must contain at least one node
     */
    public function testGetPathFromArrayException()
    {
        Magento_Filesystem::getPathFromArray(array());
    }

    /**
     * @dataProvider isPathInDirectoryDataProvider
     * @param string $path
     * @param string $directory
     * @param boolean $expectedValue
     */
    public function testIsPathInDirectory($path, $directory, $expectedValue)
    {
        $this->assertEquals($expectedValue, Magento_Filesystem::isPathInDirectory($path, $directory));
    }

    /**
     * @return array
     */
    public function isPathInDirectoryDataProvider()
    {
        return array(
            array('/tmp/file', '/tmp', true),
            array('/tmp/file', '/tmp/dir', false),
            array('/tmp', '/tmp/', true),
            array('/tmp/', '/tmp', true),
        );
    }
}
