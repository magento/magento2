<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

/**
 * @magentoAppArea adminhtml
 */
class CatalogTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogSearch\Model\Search\Catalog */
    protected $catalogSearch;

    protected function setUp()
    {
        $this->catalogSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\CatalogSearch\Model\Search\Catalog');
    }

    /**
     * Dataprovider for testLoad
     *
     * @return array
     */
    public function dataProviderForTestLoad()
    {
        return [
            ['StoreTitle', true], // positive case
            ['Some other', false], // negative case
        ];
    }

    /**
     * Check that we can find product by name from previous
     *
     * @dataProvider dataProviderForTestLoad
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple_multistore.php
     *
     * @param string $viewName
     * @param bool $expectedResult
     * @return void
     */
    public function testLoad($viewName, $expectedResult)
    {
        $results = $this->catalogSearch->setStart(0)
            ->setLimit(20)
            ->setQuery($viewName)
            ->load()
            ->getResults();
        $result = false;
        $product = array_shift($results);
        if ($product
            && $product['type'] == 'Product'
            && $product['name'] == 'Simple Product One'
        ) {
            $result = true;
        }
        $this->assertEquals(
            $expectedResult,
            $result,
            'Can\'t find product "Simple Product One" with name "StoreTitle" on second store view'
        );
    }
}
