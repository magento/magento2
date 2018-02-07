<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Simplexml\Test\Unit\Config\Cache;

use Magento\Framework\Simplexml\Config\Cache\File;

class AbstractCacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var File */
    protected $cache;

    protected $file;

    protected function setUp()
    {
        $this->cache = new File();
        $this->file = realpath(__DIR__ . '/../../_files/data.xml');
    }

    public function testAddComponent()
    {
        $this->cache->addComponent('wrong_path');
        $this->assertSame([], $this->cache->getComponents());

        $this->cache->addComponent($this->file);
        $this->assertSame([$this->file => ['mtime' => filemtime($this->file)]], $this->cache->getComponents());
    }

    public function testValidateComponents()
    {
        $this->assertSame(false, $this->cache->validateComponents([]));
        $this->assertSame(false, $this->cache->validateComponents(''));
        $this->assertSame(false, $this->cache->validateComponents([$this->file => ['mtime' => '']]));
        $this->assertSame(false, $this->cache->validateComponents([$this->file => ['mtime' => 1]]));
        $this->assertSame(true, $this->cache->validateComponents([$this->file => ['mtime' => filemtime($this->file)]]));
    }

    public function testGetComponentsHash()
    {
        $this->cache->addComponent($this->file);
        $this->assertSame(md5(filemtime($this->file) . ':'), $this->cache->getComponentsHash());
    }
}
