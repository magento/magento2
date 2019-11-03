<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Filesystem;

use \Magento\Framework\App\Filesystem\DirectoryList;

class DirectoryListTest extends \PHPUnit\Framework\TestCase
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
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);
        $this->expectExceptionMessage("Unknown directory type: 'unknown'");
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
        $defaultConfig = DirectoryList::getDefaultConfig();

        $this->assertArrayHasKey(DirectoryList::GENERATED, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::GENERATED_METADATA, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::GENERATED_CODE, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::ROOT, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::APP, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::CONFIG, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::LIB_INTERNAL, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::VAR_DIR, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::CACHE, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::LOG, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::SESSION, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::MEDIA, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::STATIC_VIEW, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::PUB, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::LIB_WEB, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::TMP, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::UPLOAD, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::TEMPLATE_MINIFICATION_DIR, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::TMP_MATERIALIZATION_DIR, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::SETUP, $defaultConfig);
        $this->assertArrayHasKey(DirectoryList::COMPOSER_HOME, $defaultConfig);
    }
}
