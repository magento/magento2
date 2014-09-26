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

namespace Magento\Reports\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Reports\Test\Page\Adminhtml\SearchIndex;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Test Creation for SearchTermsReportEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Products is created.
 *
 * Steps:
 * 1. Search products in frontend.
 * 2. Login to backend.
 * 3. Navigate to: Reports > Search Terms.
 * 4. Perform appropriate assertions.
 *
 * @group Search_Terms_(MX)
 * @ZephyrId MAGETWO-27106
 */
class SearchTermsReportEntityTest extends Injectable
{
    /**
     * Index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Search Index page
     *
     * @var SearchIndex
     */
    protected $searchIndex;

    /**
     * FixtureFactory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages
     *
     * @param CmsIndex $cmsIndex
     * @param SearchIndex $searchIndex
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(CmsIndex $cmsIndex, SearchIndex $searchIndex, FixtureFactory $fixtureFactory)
    {
        $this->cmsIndex = $cmsIndex;
        $this->searchIndex = $searchIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Search Terms Report
     *
     * @param CatalogProductSimple $product
     * @param int $countProducts
     * @param int $countSearch
     * @return array
     */
    public function test(CatalogProductSimple $product, $countProducts, $countSearch)
    {
        $this->markTestIncomplete('MAGETWO-27150');
        // Preconditions
        $productName = $this->createProducts($product, $countProducts);

        // Steps
        $this->cmsIndex->open();
        $this->searchProducts($productName, $countSearch);
        $this->searchIndex->open();

        return ['productName' => $productName];
    }

    /**
     * Create products
     *
     * @param CatalogProductSimple $product
     * @param int $countProduct
     * @return string
     */
    protected function createProducts(CatalogProductSimple $product, $countProduct)
    {
        for ($i = 0; $i < $countProduct; $i++) {
            $product->persist();
        }
        return $product->getName();
    }

    /**
     * Search products
     *
     * @param string $productName
     * @param int $countSearch
     * @return void
     */
    protected function searchProducts($productName, $countSearch)
    {
        for ($i = 0; $i < $countSearch; $i++) {
            $this->cmsIndex->getSearchBlock()->search($productName);
        }
    }
}
