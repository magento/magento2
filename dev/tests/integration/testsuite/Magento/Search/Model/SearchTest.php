<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Search test.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\AdapterInterface
     */
    private $adapter;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $searchEngine = EngineResolver::CATALOG_SEARCH_MYSQL_ENGINE;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    private $requestBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create(\Magento\Framework\Search\Request\Config::class);

        $this->requestBuilder = $this->objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );

        $this->adapter = $this->createAdapter();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Returns search adapter instance.
     *
     * @return \Magento\Framework\Search\AdapterInterface
     */
    protected function createAdapter()
    {
        return $this->objectManager->create(\Magento\Framework\Search\Adapter\Mysql\Adapter::class);
    }

    /**
     * Make sure that correct engine is set.
     *
     * @return void
     */
    protected function assertPreConditions()
    {
        $currentEngine = $this->objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->getValue(EngineInterface::CONFIG_ENGINE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->assertEquals($this->searchEngine, $currentEngine);
    }

    /**
     * Execute search query.
     *
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    private function executeQuery()
    {
        /** @var \Magento\Framework\Search\RequestInterface $queryRequest */
        $queryRequest = $this->requestBuilder->create();
        $queryResponse = $this->adapter->query($queryRequest);

        return $queryResponse;
    }

    /**
     * Returns document ids from query response.
     *
     * @param \Magento\Framework\Search\Response\QueryResponse $queryResponse
     * @return array
     */
    protected function getProductIds(\Magento\Framework\Search\Response\QueryResponse $queryResponse)
    {
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $actualIds[] = $document->getId();
        }

        return $actualIds;
    }

    /**
     * Search grouped product.
     *
     * @magentoDataFixture Magento/Framework/Search/_files/grouped_product.php
     * @magentoConfigFixture current_store catalog/search/engine mysql
     *
     * @return void
     */
    public function testSearchGroupedProduct()
    {
        $this->requestBuilder->bind('search_term', 'Grouped Product');
        $this->requestBuilder->setRequestName('quick_search_container');

        $queryResponse = $this->executeQuery();
        $result = $this->getProductIds($queryResponse);

        self::assertCount(3, $result);

        $groupedProduct = $this->productRepository->get('grouped-product');
        self::assertContains($groupedProduct->getId(), $result, 'Grouped product not found by name.');
    }
}
