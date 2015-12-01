<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig;
use Magento\Framework\Config\Data;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigTest
 */
class EsConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig
     */
    protected $config;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->reader = $this->getMockBuilder('Magento\Elasticsearch\Model\Adapter\Index\Config\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache = $this->getMockBuilder('Magento\Framework\Config\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache->expects($this->any())
            ->method('load')
            ->willReturn('a:3:{i:1;s:6:"elem 1";i:2;s:6:"elem 2";i:3;s:7:" elem 3";}');

        $this->config = new EsConfig(
            $this->reader,
            $this->cache,
            'elasticsearch_index_config'
        );
    }

    /**
     * @return array|mixed|null
     */
    public function testGetStemmerInfo()
    {
        $this->config->getStemmerInfo();
    }
}
