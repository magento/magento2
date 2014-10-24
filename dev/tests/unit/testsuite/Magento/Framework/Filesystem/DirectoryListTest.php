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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filesystem;

class DirectoryListTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDefaultConfig()
    {
        $this->assertArrayHasKey(DirectoryList::SYS_TMP, DirectoryList::getDefaultConfig());
    }

    public function testGetters()
    {
        $customDirs = [
            'foo' => [DirectoryList::PATH => '/foo/dir'],
            DirectoryList::SYS_TMP => [DirectoryList::PATH => '/bar/dir', DirectoryList::URL_PATH => 'bar']
        ];
        $object = new DirectoryList('/root/dir', $customDirs);
        $this->assertEquals('/bar/dir', $object->getPath(DirectoryList::SYS_TMP));
        $this->assertEquals('bar', $object->getUrlPath(DirectoryList::SYS_TMP));
        $this->assertEquals('/root/dir', $object->getRoot());
    }

    /**
     * @param string $method
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     * @expectedExceptionMessage Unknown directory type: 'foo'
     * @dataProvider assertCodeDataProvider
     */
    public function testAssertCode($method)
    {
        $object = new DirectoryList('/root/dir');
        $object->$method('foo');
    }

    /**
     * @return array
     */
    public function assertCodeDataProvider()
    {
        return [['getPath', 'getUrlPath']];
    }

    /**
     * @param array $config
     * @param string|bool $expected
     * @dataProvider getUrlPathDataProvider
     */
    public function testGetUrlPath($config, $expected)
    {
        $object = new DirectoryList('/root/dir', $config);
        $this->assertEquals($expected, $object->getUrlPath(DirectoryList::SYS_TMP));
    }

    /**
     * @return array
     */
    public function getUrlPathDataProvider()
    {
        return [
            [[], false],
            [[DirectoryList::SYS_TMP => [DirectoryList::URL_PATH => 'url/path']], 'url/path'],
        ];
    }

    public function testFilterPath()
    {
        $object = new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::PATH => 'C:\Windows\Tmp']]);
        $this->assertEquals('C:/Windows/Tmp', $object->getPath(DirectoryList::SYS_TMP));
    }

    public function testPrependRoot()
    {
        $object = new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::PATH => 'tmp']]);
        $this->assertEquals('/root/dir/tmp', $object->getPath(DirectoryList::SYS_TMP));
    }

    /**
     * @param string $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage URL path must be relative directory path in lowercase with '/' directory separator:
     * @dataProvider assertUrlPathDataProvider
     */
    public function testAssertUrlPath($value)
    {
        new DirectoryList('/root/dir', [DirectoryList::SYS_TMP => [DirectoryList::URL_PATH => $value]]);
    }

    /**
     * @return array
     */
    public function assertUrlPathDataProvider()
    {
        return [
            ['/'],
            ['//'],
            ['/value'],
            ['value/'],
            ['/value/'],
            ['one\\two'],
            ['../dir'],
            ['./dir'],
            ['one/../two']
        ];
    }
}
