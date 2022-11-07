<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\OpenSearch\Model\SearchClient;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ConnectionManagerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConnectionManager
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
     * Test if 'opensearch' search engine returned by connection manager.
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/catalog/search/engine opensearch
     */
    public function testCorrectSearchClientOpenSearch()
    {
        $connection = $this->connectionManager->getConnection();
        $this->assertInstanceOf(SearchClient::class, $connection);
    }
}
