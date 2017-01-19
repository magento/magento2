<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Filesystem;

use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\Generator\Io;

class DirectoryListTest extends \PHPUnit_Framework_TestCase
{
    public function testRoot()
    {
        $object = new DirectoryList('/root/dir');
        $this->assertEquals($object->getRoot(), $object->getPath(DirectoryList::ROOT));
    }

    public function testDirectoriesCustomization()
    {
        $config = [DirectoryList::APP => [DirectoryList::PATH => 'foo', DirectoryList::URL_PATH => 'bar']];
        $object = new DirectoryList('/root/dir', $config);
        $this->assertFileExists($object->getPath(DirectoryList::SYS_TMP));
        $this->assertEquals('/root/dir/foo', $object->getPath(DirectoryList::APP));
        $this->assertEquals('bar', $object->getUrlPath(DirectoryList::APP));
        $this->setExpectedException(
            \Magento\Framework\Exception\FileSystemException::class,
            "Unknown directory type: 'unknown'"
        );
        $object->getPath('unknown');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown type: test
     */
    public function testUnknownDirectory()
    {
        new DirectoryList('/root/dir', ['test' => [DirectoryList::PATH => '/baz']]);
    }

    public function testGetDefaultConfig()
    {
        $this->assertEquals(
            [
                DirectoryList::ROOT => [DirectoryList::PATH => ''],
                DirectoryList::APP => [DirectoryList::PATH => 'app'],
                DirectoryList::CONFIG => [DirectoryList::PATH => 'app/etc'],
                DirectoryList::LIB_INTERNAL => [DirectoryList::PATH => 'lib/internal'],
                DirectoryList::VAR_DIR => [DirectoryList::PATH => 'var'],
                DirectoryList::CACHE => [DirectoryList::PATH => 'var/cache'],
                DirectoryList::LOG => [DirectoryList::PATH => 'var/log'],
                DirectoryList::DI => [DirectoryList::PATH => 'generated/metadata'],
                DirectoryList::GENERATION => [DirectoryList::PATH => Io::DEFAULT_DIRECTORY],
                DirectoryList::SESSION => [DirectoryList::PATH => 'var/session'],
                DirectoryList::MEDIA => [DirectoryList::PATH => 'pub/media', DirectoryList::URL_PATH => 'pub/media'],
                DirectoryList::STATIC_VIEW => [
                    DirectoryList::PATH => 'pub/static',
                    DirectoryList::URL_PATH => 'pub/static'
                ],
                DirectoryList::PUB => [DirectoryList::PATH => 'pub', DirectoryList::URL_PATH => 'pub'],
                DirectoryList::LIB_WEB => [DirectoryList::PATH => 'lib/web'],
                DirectoryList::TMP => [DirectoryList::PATH => 'var/tmp'],
                DirectoryList::UPLOAD => [
                    DirectoryList::PATH => 'pub/media/upload',
                    DirectoryList::URL_PATH => 'pub/media/upload'
                ],
                DirectoryList::TMP_MATERIALIZATION_DIR => [DirectoryList::PATH => 'var/view_preprocessed'],
                DirectoryList::TEMPLATE_MINIFICATION_DIR => [DirectoryList::PATH => 'var/view_preprocessed/html'],
                DirectoryList::SETUP => [DirectoryList::PATH => 'setup/src'],
                DirectoryList::COMPOSER_HOME => [DirectoryList::PATH => 'var/composer_home'],
                DirectoryList::GENERATED => [DirectoryList::PATH => 'generated'],
                DirectoryList::SYS_TMP => [DirectoryList::PATH => '']
            ],
            DirectoryList::getDefaultConfig()
        );
    }
}
