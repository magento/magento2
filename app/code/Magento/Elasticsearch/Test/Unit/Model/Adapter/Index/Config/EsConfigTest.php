<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigTest
 */
class EsConfigTest extends TestCase
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
     * @var \Magento\Elasticsearch\Model\Adapter\Index\Config\Reader|MockObject
     */
    protected $reader;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cache;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->reader = $this->getMockBuilder(ReaderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->cache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->cache->expects($this->any())
            ->method('load')
            ->willReturn('serializedData');

        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn(['unserializedData']);

        $this->config = $this->objectManager->getObject(
            EsConfig::class,
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
