<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\SearchIndexNameResolver;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SearchIndexNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchIndexNameResolver
     */
    protected $model;

    /**
     * @var ClientOptionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientConfig;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $entityType;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * Setup method
     * @return void
     */
    public function setUp()
    {
        $this->clientConfig = $this->getMockBuilder('Magento\Elasticsearch\Model\Config')
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

        $this->entityType = 'catalogsearch_fulltext';
        $this->storeId = 1;

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\Index\SearchIndexNameResolver',
            [
                'clientConfig' => $this->clientConfig,
                'options' => [],
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
            $this->model->getIndexName($this->storeId, $this->entityType)
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

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getClientOptions()
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'my-password',
        ];
    }
}
