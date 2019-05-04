<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Config\Data;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigTest
 */
class EsConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var EsConfig
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
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->reader = $this->getMockBuilder(\Magento\Framework\Config\ReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache = $this->getMockBuilder(\Magento\Framework\Config\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache->expects($this->any())
            ->method('load')
            ->willReturn('serializedData');

        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn(['unserializedData']);

        $this->config = $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig::class,
            [
                'reader' => $this->reader,
                'cache' => $this->cache,
                'serializer' => $this->serializerMock,
            ]
        );
    }

    /**
     * @return array|mixed|null
     */
    public function testGetStemmerInfo()
    {
        $this->config->getStemmerInfo();
    }

    /**
     * @return array|mixed|null
     */
    public function testGetStopwordsInfo()
    {
        $this->config->getStopwordsInfo();
    }
}
