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

namespace Magento\Backend\Model\Search;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class CatalogTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Model\Search\Catalog */
    protected $catalogSearch;

    protected function setUp()
    {
        $this->catalogSearch = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('\Magento\Backend\Model\Search\Catalog');
    }

    /**
     * Dataprovider for testLoad
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
