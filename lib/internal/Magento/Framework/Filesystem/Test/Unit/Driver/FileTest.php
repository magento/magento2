<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Driver;

use Magento\Framework\Filesystem\Driver\File as FileDriver;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider relativePathDataProvider
     */
    public function testGetRelativePath($basePath, $path, $expected)
    {
        $this->assertSame($expected, (new FileDriver())->getRelativePath($basePath, $path));
    }

    public function relativePathDataProvider()
    {
        return [
            'path within bp' => ['/base/path', '/base/path/file', '/file'],
            'path within bp, bp with /' => ['/base/path/', '/base/path/file', 'file'],
            'path within bp, path with /' => ['/base/path/', '/base/path/file/', 'file/'],
            
            'path eq bp, with /' => ['/base/path/', '/base/path/', ''],
            'path eq bp, no /' => ['/base/path', '/base/path', ''],
            'path eq bp, path no /' => ['/base/path/', '/base/path', ''],
            'path eq bp, path with /' => ['/base/path', '/base/path/', '/'],
            
            'path relative, path no /' => ['/base/path', 'relative/path', 'relative/path'],
            'path relative, path no /, bp with /' => ['/base/path/', 'relative/file', 'relative/file'],
            'path relative, path with /' => ['/base/path', 'relative/file/', 'relative/file/'],
            'path relative, path with /, bp with /' => ['/base/path/', 'relative/file/', 'relative/file/'],
            
            'bp is root' => ['/', '/path/to/file', 'path/to/file'],
            'path is root' => ['/base/path/dir', '/', '../../../'],

            'path is parent of bp' => ['/base/path', '/base', '..'],
            'path is parent of bp, path with /' => ['/base/path', '/base/', '../'],
            'path is grandparent of bp' => ['/base/path/dir', '/base', '../..'],
            'path is grandparent of bp, path with /' => ['/base/path/dir', '/base/', '../../'],
            
            'path one up one down' => ['/base/path/dir', '/base/path/another-dir', '../another-dir'],
            'path one up one down, path has /' => ['/base/path/dir', '/base/path/another-dir/', '../another-dir/'],
            
            'no shared parent' => ['/one/dir/path', '/another/dir/path', '../../../another/dir/path'],
        ];
    }
}
