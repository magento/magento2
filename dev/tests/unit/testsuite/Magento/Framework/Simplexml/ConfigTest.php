<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Simplexml;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        $this->config = new Config();
    }

    public function testConstruct()
    {
        $xml = '<root><node1><node2/></node1><node3><node4/></node3></root>';
        $file = __DIR__ . '/_files/data.xml';

        $config = new Config($xml);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());

        $config = new Config($file);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());

        /** @var Element $simpleXml */
        $simpleXml = simplexml_load_string(file_get_contents($file), 'Magento\Framework\Simplexml\Element');
        $config = new Config($simpleXml);
        $this->assertXmlStringEqualsXmlString($xml, $config->getXmlString());
    }

    public function testLoadString()
    {
        $xml = '<?xml version="1.0"?><config><node>1</node></config>';
        $this->assertFalse($this->config->loadString(''));
        $this->assertTrue($this->config->loadString($xml));
        $this->assertXmlStringEqualsXmlString($xml, $this->config->getXmlString());
        $this->setExpectedException(
            '\Exception',
            'simplexml_load_string(): Entity: line 1: parser error : Start tag expected,'
        );
        $this->assertFalse($this->config->loadString('wrong_path'));
    }

    public function testLoadDom()
    {
        $this->config->loadString('<?xml version="1.0"?><config><node>1</node></config>');
        $this->assertTrue($this->config->loadDom($this->config->getNode()));
    }

    public function testGetNode()
    {
        $this->assertFalse($this->config->getNode());
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $this->assertSame('Value 2.1', $config->getNode('node_2/node_2_1')->asArray());
    }

    public function testGetXpath()
    {
        $this->assertFalse($this->config->getXpath('wrong_xpath'));
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $this->assertFalse($config->getXpath('wrong_xpath'));
        $element = $config->getXpath('/root/node_2/node_2_1');
        $this->assertArrayHasKey(0, $element);
        $this->assertInstanceOf('Magento\Framework\Simplexml\Element', $element[0]);
        $this->assertSame('Value 2.1', $element[0]->asArray());
    }

    public function testLoadWrongFile()
    {
        $this->assertFalse($this->config->loadFile('wrong_file'));
    }

    public function testSetCacheChecksum()
    {
        $this->config->setCacheChecksum(null);
        $this->assertNull($this->config->getCacheChecksum());
        $this->config->setCacheChecksum(false);
        $this->assertFalse($this->config->getCacheChecksum());
        $this->config->setCacheChecksum(0);
        $this->assertFalse($this->config->getCacheChecksum());
        $this->config->setCacheChecksum('CacheChecksum');
        $this->assertSame('415a5472d4f94b71ff80fd1c8e9eca7f', $this->config->getCacheChecksum());
    }

    public function testUpdateCacheChecksum()
    {
        $this->config->setCacheChecksum('CacheChecksum');
        $this->config->updateCacheChecksum(false);
        $this->assertFalse($this->config->getCacheChecksum());

        $this->config->setCacheChecksum('CacheChecksum');
        $this->config->updateCacheChecksum(0);
        $this->assertFalse($this->config->getCacheChecksum());

        $this->config->setCacheChecksum('CacheChecksum');
        $this->config->updateCacheChecksum('UpdateCacheChecksum');
        $this->assertSame('894eb161d8e1e48f825d05fdac61afae', $this->config->getCacheChecksum());

        $this->config->setCacheChecksum(false);
        $this->config->updateCacheChecksum('UpdateCacheChecksum');
        $this->assertFalse($this->config->getCacheChecksum());
    }

    public function testValidateCacheChecksum()
    {
        $this->config->setCacheChecksum(false);
        $this->assertFalse($this->config->validateCacheChecksum());

        $this->config->setCacheChecksum(null);
        $this->assertTrue($this->config->validateCacheChecksum());

        $this->config->setCacheId('cacheId');
        $this->config->setCacheChecksum('CacheChecksum');
        $cache = $this->getMock('Magento\Framework\Simplexml\Config\Cache\File', ['load']);
        $cache->expects($this->once())->method('load')->with('cacheId__CHECKSUM')
            ->will($this->returnValue('415a5472d4f94b71ff80fd1c8e9eca7f'));
        $this->config->setCache($cache);
        $this->assertTrue($this->config->validateCacheChecksum());
    }

    public function testLoadCache()
    {
        $this->config->setCacheChecksum(false);
        $this->assertFalse($this->config->loadCache());

        $this->config->setCacheId('cacheId');
        $this->config->setCacheChecksum('CacheChecksum');
        $cache = $this->getMock('Magento\Framework\Simplexml\Config\Cache\File', ['load']);
        $this->config->setCache($cache);

        $cache->expects($this->at(0))->method('load')->with('cacheId__CHECKSUM')
            ->will($this->returnValue('415a5472d4f94b71ff80fd1c8e9eca7f'));
        $cache->expects($this->at(1))->method('load')->with('cacheId')
            ->will($this->returnValue(''));
        $this->config->setCache($cache);
        $cache->expects($this->at(2))->method('load')->with('cacheId__CHECKSUM')
            ->will($this->returnValue('415a5472d4f94b71ff80fd1c8e9eca7f'));
        $cache->expects($this->at(3))->method('load')->with('cacheId')
            ->will($this->returnValue('<?xml version="1.0"?><config><node>1</node></config>'));

        $this->assertFalse($this->config->loadCache());
        $this->assertTrue($this->config->loadCache());
    }

    public function testSaveCache()
    {
        $xml = '<config><node>1</node></config>';

        $cache = $this->getMock('Magento\Framework\Simplexml\Config\Cache\File', ['save']);
        $cache->expects($this->at(0))->method('save')
            ->with(null, 'cacheId__CHECKSUM', ['cacheTags'], 10)
            ->will($this->returnValue(true));
        $cache->expects($this->at(1))->method('save')
            ->with($xml, 'cacheId', ['cacheTags'], 10)
            ->will($this->returnValue(true));
        $cache->expects($this->exactly(2))->method('save');

        $this->config->loadString($xml);
        $this->config->setCache($cache);
        $this->config->setCacheChecksum(null);
        $this->config->setCacheTags(['cacheTags']);
        $this->config->setCacheId('cacheId');
        $this->config->setCacheLifetime(10);

        $this->config->saveCache();
        $this->config->saveCache();
        $this->config->setCacheSaved(false);
        $this->config->setCacheChecksum(false);
        $this->config->saveCache();
    }

    public function testRemoveCache()
    {
        $cache = $this->getMock('Magento\Framework\Simplexml\Config\Cache\File', ['remove']);
        $cache->expects($this->at(0))->method('remove')
            ->with('cacheId')
            ->will($this->returnValue(true));
        $cache->expects($this->at(1))->method('remove')
            ->with('cacheId__CHECKSUM')
            ->will($this->returnValue(true));
        $cache->expects($this->exactly(2))->method('remove');

        $this->config->setCache($cache);
        $this->config->setCacheId('cacheId');
        $this->config->removeCache();
    }

    public function testSetNode()
    {
        $config = new Config(__DIR__ . '/_files/mixed_data.xml');
        $config->setNode('node_2', 'new_value');
        $this->assertSame('new_value', $config->getNode('node_2')->asArray());
    }

    public function testApplyExtends()
    {
        $config = new Config(__DIR__ . '/_files/extend_data.xml');
        $config->applyExtends();
        $this->assertEquals(
            $config->getNode('node_1/node_1_1')->asArray(),
            $config->getNode('node_3/node_1_1')->asArray()
        );
        $config = new Config(__DIR__ . '/_files/data.xml');
        $config->applyExtends();
    }

    public function testExtendNode()
    {
        $config = new Config(__DIR__ . '/_files/data.xml');
        $config->extend(new Config('<config><node>1</node></config>'));
        $this->assertSame('1', $config->getNode('node')->asArray());
    }
}
