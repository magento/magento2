<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch7\Model\Client\Elasticsearch;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ConnectionManagerTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ConnectionManager
     */
    private $connectionManager;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        // phpstan:ignore "Class Magento\Elasticsearch\SearchAdapter\ConnectionManager not found."
        $this->connectionManager = $this->objectManager->create(ConnectionManager::class);
    }

    /**
     * Test if 'elasticsearch7' search engine returned by connection manager.
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     */
    public function testCorrectElasticsearchClientEs7()
    {
        if (!class_exists(\Elasticsearch\ClientBuilder::class)) { /** @phpstan-ignore-line */
            $this->markTestSkipped('AC-6597: Skipped as Elasticsearch 8 is configured');
        }

        $connection = $this->connectionManager->getConnection();
        $this->assertInstanceOf(Elasticsearch::class, $connection);
    }
}
