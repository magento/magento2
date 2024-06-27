<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * \Magento\Framework\Cache\Core test case
 */
namespace Magento\Framework\Cache\Test\Unit;

use Magento\Framework\Cache\Backend\Decorator\AbstractDecorator;
use Magento\Framework\Cache\Backend\Redis;
use Magento\Framework\Cache\Core;
use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Cache\Frontend\Decorator\Bare;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\TestCase;
use Zend_Cache_Exception;

class CoreTest extends TestCase
{
    /**
     * @var Core
     */
    protected $_core;

    /**
     * @var array
     */
    protected static $_cacheStorage = [];

    /**
     * Selected mock of \Zend_Cache_Backend_File to have extended
     * \Zend_Cache_Backend and implemented \Zend_Cache_Backend_Interface
     *
     * @var \Zend_Cache_Backend_File
     */
    protected $_mockBackend;

    protected function setUp(): void
    {
        $this->_mockBackend = $this->createMock(\Zend_Cache_Backend_File::class);
    }

    protected function tearDown(): void
    {
        unset($this->_mockBackend);
    }

    public function testSetBackendDefault()
    {
        $core = new Core();
        $core->setBackend($this->_mockBackend);

        $this->assertNotInstanceOf(
            AbstractDecorator::class,
            $core->getBackend()
        );
        $this->assertEquals($this->_mockBackend, $core->getBackend());
    }

    /**
     * @dataProvider setBackendExceptionProvider
     */
    public function testSetBackendException($decorators)
    {
        $this->expectException('Zend_Cache_Exception');
        $core = new Core(['backend_decorators' => $decorators]);
        $core->setBackend($this->_mockBackend);
    }

    /**
     * @return array
     */
    public function setBackendExceptionProvider()
    {
        return [
            'string' => ['string'],
            'decorator setting is not an array' => [['decorator' => 'string']],
            'decorator setting is empty array' => [['decorator' => []]],
            'no class index in array' => [['decorator' => ['somedata']]],
            'non-existing class passed' => [['decorator' => ['class' => 'NonExistingClass']]]
        ];
    }

    public function testSaveDisabled()
    {
        $backendMock = $this->createMock(\Zend_Cache_Backend_BlackHole::class);
        $backendMock->expects($this->never())->method('save');
        $frontend = new Core(['disable_save' => true]);
        $frontend->setBackend($backendMock);
        $result = $frontend->save('data', 'id');
        $this->assertTrue($result);
    }

    public function testSaveNoCaching()
    {
        $backendMock = $this->createMock(\Zend_Cache_Backend_BlackHole::class);
        $backendMock->expects($this->never())->method('save');
        $frontend = new Core(['disable_save' => false, 'caching' => false]);
        $frontend->setBackend($backendMock);
        $result = $frontend->save('data', 'id');
        $this->assertTrue($result);
    }

    public function testSave()
    {
        $data = 'data';
        $tags = ['abc', '!def', '_ghi'];
        $prefix = 'prefix_';
        $prefixedTags = ['prefix_abc', 'prefix__def', 'prefix__ghi'];

        $backendMock = $this->createMock(\Zend_Cache_Backend_BlackHole::class);
        $backendMock->expects($this->once())
            ->method('save')
            ->with($data, $this->anything(), $prefixedTags)
            ->willReturn(true);
        $backendMock->expects($this->once())
            ->method('getCapabilities')
            ->willReturn(['priority' => null]);
        $frontend = new Core([
            'disable_save'              => false,
            'caching'                   => true,
            'cache_id_prefix'           => $prefix,
            'automatic_cleaning_factor' => 0,
            'write_control'             => false,
        ]);
        $frontend->setBackend($backendMock);
        $result = $frontend->save($data, 'id', $tags);
        $this->assertTrue($result);
    }

    public function testClean()
    {
        $mode = 'all';
        $tags = ['abc', '!def', '_ghi'];
        $prefix = 'prefix_';
        $prefixedTags = ['prefix_abc', 'prefix__def', 'prefix__ghi'];
        $expectedResult = true;

        $backendMock = $this->createMock(\Zend_Cache_Backend_BlackHole::class);
        $backendMock->expects($this->once())
            ->method('clean')
            ->with($mode, $prefixedTags)
            ->willReturn($expectedResult);
        $frontend = new Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix,
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->clean($mode, $tags);
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetIdsMatchingTags()
    {
        $tags = ['abc', '!def', '_ghi'];
        $prefix = 'prefix_';
        $prefixedTags = ['prefix_abc', 'prefix__def', 'prefix__ghi'];
        $ids = ['id', 'id2', 'id3'];

        $backendMock = $this->createMock(CoreTestMock::class);
        $backendMock->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with($prefixedTags)
            ->willReturn($ids);
        $backendMock->expects($this->any())
            ->method('getCapabilities')
            ->willReturn(['tags' => true]);
        $frontend = new Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix,
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->getIdsMatchingTags($tags);
        $this->assertEquals($ids, $result);
    }

    public function testGetIdsNotMatchingTags()
    {
        $tags = ['abc', '!def', '_ghi'];
        $prefix = 'prefix_';
        $prefixedTags = ['prefix_abc', 'prefix__def', 'prefix__ghi'];
        $ids = ['id', 'id2', 'id3'];

        $backendMock = $this->createMock(CoreTestMock::class);
        $backendMock->expects($this->once())
            ->method('getIdsNotMatchingTags')
            ->with($prefixedTags)
            ->willReturn($ids);
        $backendMock->expects($this->any())
            ->method('getCapabilities')
            ->willReturn(['tags' => true]);
        $frontend = new Core([
            'caching'         => true,
            'cache_id_prefix' => $prefix,
        ]);
        $frontend->setBackend($backendMock);

        $result = $frontend->getIdsNotMatchingTags($tags);
        $this->assertEquals($ids, $result);
    }

    public function testLoadAllowsToUseCurlyBracketsInPrefixOnRedisBackend()
    {
        $id = 'abc';

        $mockBackend = $this->createMock(Redis::class);
        $core = new Core([
            'cache_id_prefix' => '{prefix}_'
        ]);
        $core->setBackend($mockBackend);

        $core->load($id);
        $this->assertNull(null);
    }

    public function testLoadNotAllowsToUseCurlyBracketsInPrefixOnNonRedisBackend()
    {
        $id = 'abc';

        $core = new Core([
            'cache_id_prefix' => '{prefix}_'
        ]);
        $core->setBackend($this->_mockBackend);

        $this->expectException(Zend_Cache_Exception::class);
        $this->expectExceptionMessage("Invalid id or tag '{prefix}_abc' : must use only [a-zA-Z0-9_]");

        $core->load($id);
    }
}
