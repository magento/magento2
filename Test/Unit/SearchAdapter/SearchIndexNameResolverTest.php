<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
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
    protected $indexId;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * Setup method
     * @return void
     */
    protected function setUp()
    {
        $this->clientConfig = $this->getMockBuilder(\Magento\Elasticsearch\Model\Config::class)
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
            \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver::class,
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
