<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index\Config;

use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Config\Data;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigTest
 */
class EsConfigTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

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
            ->willReturn('["elem 1","elem 2"," elem 3"]');

        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);

        $this->jsonMock->method('decode')
            ->willReturn(['elem 1', 'elem 2', ' elem 3']);

        $this->objectManager->mockObjectManager([\Magento\Framework\Json\JsonInterface::class => $this->jsonMock]);

        $this->config = $this->objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfig::class,
            [
                'reader' => $this->reader,
                'cache' => $this->cache
            ]
        );
    }

    protected function tearDown()
    {
        $this->objectManager->restoreObjectManager();
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
