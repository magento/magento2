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
namespace Magento\Framework\Simplexml\Config\Cache;

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
