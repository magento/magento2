<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\ElasticAdapter\SearchAdapter;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class AdapterTest to test Elasticsearch search adapter
 */
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Adapter
     */
    private $adapter;

    /**
     * @var \Magento\AdvancedSearch\Model\Client\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $contentManager = $this->getMockBuilder(\Magento\Elasticsearch\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(\Magento\AdvancedSearch\Model\Client\ClientInterface::class)
            ->setMethods(['query', 'testConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->clientMock);
        /** @var \Magento\Framework\Search\Request\Config\Converter $converter */
        $converter = $objectManager->create(\Magento\Framework\Search\Request\Config\Converter::class);

        $document = new \DOMDocument();
        $document->load($this->getRequestConfigPath());
        $requestConfig = $converter->convert($document);

        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $objectManager->create(\Magento\Framework\Search\Request\Config::class);
        $config->merge($requestConfig);

        $this->requestBuilder = $objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );
        $this->loggerMock = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);

        $this->adapter = $objectManager->create(
            \Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Adapter::class,
            [
                'connectionManager' => $contentManager,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testQuery()
    {
        $this->requestBuilder->bind('fulltext_search_query', 'socks');
        $this->requestBuilder->setRequestName('one_match');
        $queryRequest = $this->requestBuilder->create();
        $exception = new \Exception('Test Message');
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);
        $this->clientMock->expects($this->once())->method('query')->willThrowException($exception);
        $actualResponse = $this->adapter->query($queryRequest);
        $this->assertEmpty($actualResponse->getAggregations()->getBuckets());
        $this->assertEquals(0, $actualResponse->count());
    }

    /**
     * Get request config path
     *
     * @return string
     */
    private function getRequestConfigPath()
    {
        return __DIR__ . '/../../_files/requests.xml';
    }
}
