<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\TestFramework\Helper\Bootstrap;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Adapter
     */
    private $adapter;

    /**
     * @var \Magento\Framework\Search\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Search\Request\Config\Converter $converter */
        $converter = $this->objectManager->create('Magento\Framework\Search\Request\Config\Converter');

        $document = new \DOMDocument();
        $document->load(__DIR__ . '/../../_files/requests.xml');
        $requestConfig = $converter->convert($document);

        /** @var \Magento\Framework\Search\Request\Config $config */
        $config = $this->objectManager->create('Magento\Framework\Search\Request\Config');
        $config->merge($requestConfig);

        /** @var \Magento\Framework\Search\RequestFactory $requestFactory */
        $this->requestFactory = $this->objectManager->create(
            'Magento\Framework\Search\RequestFactory',
            ['config' => $config]
        );

        $this->adapter = $this->objectManager->create('Magento\Framework\Search\Adapter\Mysql\Adapter');
    }

    /**
     * Sample test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testMatchQuery()
    {
        $bindValues = [
            '%request.title%' => 'socks',
        ];
        $requestName = 'one_match';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Sample test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testMatchQueryFilters()
    {
        $bindValues = [
            '%request.title%' => 'socks',
            '%pidm_from%' => 1,
            '%pidm_to%' => 3,
            '%pidsh%' => 4
        ];
        $requestName = 'one_match_filters';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(1, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testRangeFilterWithAllFields()
    {
        $bindValues = [
            '%request.product_id.from%' => 1,
            '%request.product_id.to%' => 3,
        ];
        $requestName = 'range_filter';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(2, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testRangeFilterWithoutFromField()
    {
        $bindValues = [
            '%request.product_id.to%' => 4,
        ];
        $requestName = 'range_filter_without_from_field';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(3, $queryResponse->count());
    }

    /**
     * Range filter test with all fields filled
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testRangeFilterWithoutToField()
    {
        $bindValues = [
            '%request.product_id.from%' => 2,
        ];
        $requestName = 'range_filter_without_to_field';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(4, $queryResponse->count());
    }

    /**
     * Term filter test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testTermFilter()
    {
        $id = 4;

        $bindValues = [
            '%request.product_id%' => $id,
        ];
        $requestName = 'term_filter';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(1, $queryResponse->count());
        $this->assertEquals($id, $queryResponse->getIterator()->offsetGet(0)->getId());
    }

    /**
     * Bool filter test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testBoolFilter()
    {
        /*
         * TODO: Remove test skipping after fixing issue
         */
        $this->markTestSkipped('Bool filter doesn\'t work correctly and we have issue in bug tracker');
        $expectedIds = [2, 3];
        $bindValues = [
            '%request.must.range_filter1.from%' => 1,
            '%request.must.range_filter1.to%' => 5,
            '%request.should.term_filter1%' => 1,
            '%request.should.term_filter2%' => 2,
            '%request.should.term_filter3%' => 3,
            '%request.should.term_filter4%' => 4,
            '%request.not.term_filter1%' => 1,
            '%request.not.term_filter2%' => 4,
        ];
        $requestName = 'bool_filter';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals(count($expectedIds), $queryResponse->count());
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Search\Document $document */
            $actualIds[] = $document->getId();
        }
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * Sample Advanced search request test
     *
     * @dataProvider advancedSearchDataProvider
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store catalog/search/engine Magento\CatalogSearch\Model\Resource\Fulltext\Engine
     * @magentoConfigFixture current_store catalog/search/search_type 2
     * @magentoDataFixture Magento/Framework/Search/_files/products.php
     */
    public function testSimpleAdvancedSearch($bindValues, $expectedRecorsCount)
    {
        $requestName = 'advanced_search_test';

        $queryResponse = $this->executeQuery($requestName, $bindValues);
        $this->assertEquals($expectedRecorsCount, $queryResponse->count());
    }

    /**
     * @return array
     */
    public function advancedSearchDataProvider()
    {
        return array(
            [
                [
                    '%request.name%' => 'white',
                    '%request.description%' => 'shorts',
                    '%request.store_id%' => '1',
                    '%request.from_product_id%' => '3',
                    '%request.to_product_id%' => '4',
                ],
                0 // Record is not in filter range
            ],
            [
                [
                    '%request.name%' => 'white',
                    '%request.description%' => 'shorts',
                    '%request.store_id%' => '1',
                    '%request.from_product_id%' => '1',
                    '%request.to_product_id%' => '4',
                ],
                1 // One record is expected
            ],
            [
                [
                    '%request.name%' => 'white',
                    '%request.description%' => 'shorts',
                    '%request.store_id%' => '5',
                    '%request.from_product_id%' => '1',
                    '%request.to_product_id%' => '4',
                ],
                0 // store_id filter is invalid
            ],
            [
                [
                    '%request.name%' => 'black',
                    '%request.description%' => 'tshirts',
                    '%request.store_id%' => '1',
                    '%request.from_product_id%' => '1',
                    '%request.to_product_id%' => '5',
                ],
                0 // Non existing search terms
            ],
        );
    }

    private function executeQuery($requestName, $bindValues)
    {
        $this->reindexAll();

        /** @var \Magento\Framework\Search\Request $queryRequest */
        $queryRequest = $this->requestFactory->create($requestName, $bindValues);

        $queryResponse = $this->adapter->query($queryRequest);

        return $queryResponse;
    }

    private function reindexAll()
    {
        /** @var \Magento\Indexer\Model\Indexer[] $indexerList */
        $indexerList = $this->objectManager->get('\Magento\Indexer\Model\Indexer\CollectionFactory')
            ->create()
            ->getItems();

        foreach ($indexerList as $indexer) {
            $indexer->reindexAll();
        }
    }
}
