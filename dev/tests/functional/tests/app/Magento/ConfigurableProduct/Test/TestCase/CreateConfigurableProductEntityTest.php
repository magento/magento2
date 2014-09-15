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

namespace Magento\ConfigurableProduct\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Test Coverage for CreateConfigurableProductEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Two simple products are created.
 * 2. Configurable attribute with two options is created
 * 3. Configurable attribute added to Default template
 *
 * Steps:
 * 1. Go to Backend
 * 2. Open Product -> Catalog
 * 3. Click on narrow near "Add Product" button
 * 4. Select Configurable Product
 * 5. Fill in data according to data sets
 *  5.1 If field "attributeNew/dataSet" is not empty - search created attribute by putting it's name
 *      to variation Search field.
 *  5.2 If "attribute/dataSet" is not empty- create new Variation Set
 * 6. Save product
 * 7. Perform all assertions
 *
 * @group Configurable_Product_(MX)
 * @ZephyrId MAGETWO-26041
 */
class CreateConfigurableProductEntityTest extends Injectable
{
    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    protected $productIndex;

    /**
     * Page to create a product
     *
     * @var CatalogProductNew
     */
    protected $productNew;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $productIndex
     * @param CatalogProductNew $productNew
     * @return void
     */
    public function __inject(CatalogProductIndex $productIndex, CatalogProductNew $productNew)
    {
        $this->productIndex = $productIndex;
        $this->productNew = $productNew;
    }

    /**
     * Test create catalog Configurable product run
     *
     * @param ConfigurableProductInjectable $product
     * @return void
     */
    public function test(ConfigurableProductInjectable $product)
    {
        // Steps
        $this->productIndex->open();
        $this->productIndex->getGridPageActionBlock()->addProduct('configurable');
        $this->productNew->getProductForm()->fill($product);
        $this->productNew->getFormPageActions()->save($product);
    }
}
