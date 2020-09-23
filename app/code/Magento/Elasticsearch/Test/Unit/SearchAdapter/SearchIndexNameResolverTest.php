<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchIndexNameResolverTest extends TestCase
{
    /**
     * @var SearchIndexNameResolver
     */
    protected $model;

    /**
     * @var ClientOptionsInterface|MockObject
     */
    protected $clientConfig;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $indexId;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->clientConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIndexPrefix',
                'getEntityType',
                'getIndexSettings',
            ])
            ->getMock();

        $this->clientConfig->expects($this->any())
            ->method('getIndexPrefix')
            ->willReturn('indexName');

        $this->indexId = 'catalogsearch_fulltext';
        $this->storeId = 1;

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            SearchIndexNameResolver::class,
            [
                'clientConfig' => $this->clientConfig,
            ]
        );
    }

    /**
     * Test getIndexName() indexerId 'catalogsearch_fulltext'
     */
    public function testGetIndexNameCatalogSearchFullText()
    {
        $this->assertEquals(
            'indexName_product_1',
            $this->model->getIndexName($this->storeId, $this->indexId)
        );
    }

    /**
     * Test getIndexName() with any ndex
     */
    public function testGetIndexName()
    {
        $this->assertEquals(
            'indexName_else_index_id_1',
            $this->model->getIndexName($this->storeId, 'else_index_id')
        );
    }
}
