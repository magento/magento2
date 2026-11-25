<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\WebapiAsync\Controller\Rest;

use Magento\Framework\Communication\Config\Data as CommunicationConfigData;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\MessageQueue\Publisher\Config\Data as PublisherConfigData;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\Attributes\DataProvider;

class AsynchronousSchemaRequestProcessorTest extends AbstractController
{
    /**
     * Test that the rest controller returns the correct async schema response.
     *
     * @param string $path
     * @magentoAppArea webapi_rest
     */
    #[DataProvider('schemaRequestProvider')]
    public function testSchemaRequest($path)
    {
        ob_start();
        $this->dispatch($path);
        ob_end_clean();
        $schema = $this->getResponse()->getBody();

        // Check that an HTTP 202 response is visible for what is normally an HTTP 200.
        $this->assertMatchesRegularExpression('/202 Accepted/', $schema);

        // Make sure that the async interface definition is included in the response.
        $this->assertMatchesRegularExpression('/asynchronous-operations-data-async-response-interface/', $schema);
    }

    /**
     * @magentoAppArea webapi_rest
     * @magentoAppIsolation enabled
     */
    public function testBulkSchemaRequest(): void
    {
        ob_start();
        try {
            $this->dispatch('rest/async/bulk/schema?services=all');
        } finally {
            ob_end_clean();
        }

        $schema = json_decode($this->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('2.0', $schema['swagger']);
        $this->assertNotEmpty($schema['paths']);
        $this->assertNotEmpty($schema['definitions']);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    #[DataProvider('queueConfigProvider')]
    public function testQueueConfigLoadsWithNonRestAcceptHeader(string $configClass, string $accept): void
    {
        $this->getRequest()->getHeaders()->addHeaderLine('Accept', $accept);
        $cache = $this->_objectManager->get(CacheInterface::class);
        $cacheId = 'webapi_async_non_rest_accept_config';
        $cache->remove($cacheId);

        try {
            $config = $this->_objectManager->create($configClass, ['cacheId' => $cacheId]);
            $topic = 'async.magento.catalog.api.productrepositoryinterface.save.post';
            $topics = $configClass === CommunicationConfigData::class ? $config->get('topics') : $config->get();
            $this->assertArrayHasKey($topic, $topics);
        } finally {
            $cache->remove($cacheId);
        }
    }

    /**
     * @return array
     */
    public static function queueConfigProvider(): array
    {
        return [
            'communication text/plain' => [CommunicationConfigData::class, 'text/plain'],
            'publisher text/plain' => [PublisherConfigData::class, 'text/plain'],
            'communication text/html' => [CommunicationConfigData::class, 'text/html'],
            'publisher text/html' => [PublisherConfigData::class, 'text/html'],
            'communication application/json' => [CommunicationConfigData::class, 'application/json'],
            'publisher application/json' => [PublisherConfigData::class, 'application/json'],
        ];
    }

    /**
     * Response getter
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = $this->_objectManager->get(\Magento\Framework\Webapi\Rest\Response::class);
        }
        return $this->_response;
    }

    /**
     * @return array
     */
    public static function schemaRequestProvider()
    {
        return [
            ['rest/async/schema'],
            ['rest/async/schema?services=all'],
            ['rest/all/async/schema?services=all'],
            ['rest/default/async/schema?services=all'],
            ['rest/async/schema?services=all'],
        ];
    }
}
