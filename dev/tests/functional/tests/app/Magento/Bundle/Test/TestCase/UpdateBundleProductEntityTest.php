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

namespace Magento\Bundle\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Test Creation for Update BundleProductEntity
 *
 * Test Flow:
 *
 * Precondition:
 * 1. Category is created.
 * 2. Bundle product is created.
 *
 * Steps
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS > Catalog.
 * 3. Select a product in the grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click "Save".
 * 6. Perform asserts
 *
 *
 * @group Bundle_Product_(MX)
 * @ZephyrId MAGETWO-26195
 */
class UpdateBundleProductEntityTest extends Injectable
{
    /**
     * Page product on backend
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndexNewPage
     * @param CatalogProductEdit $catalogProductEditPage
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndexNewPage,
        CatalogProductEdit $catalogProductEditPage
    ) {
        $this->catalogProductIndex = $catalogProductIndexNewPage;
        $this->catalogProductEdit = $catalogProductEditPage;
    }

    /**
     * Test update bundle product
     *
     * @param BundleProduct $product
     * @param BundleProduct $originalProduct
     * @return void
     */
    public function test(BundleProduct $product, BundleProduct $originalProduct)
    {
        $originalProduct->persist();
        $this->catalogProductIndex->open();
        $filter = ['sku' => $originalProduct->getSku()];
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
        $this->catalogProductEdit->getProductForm()->fill($product);
        $this->catalogProductEdit->getFormPageActions()->save();
    }
}
