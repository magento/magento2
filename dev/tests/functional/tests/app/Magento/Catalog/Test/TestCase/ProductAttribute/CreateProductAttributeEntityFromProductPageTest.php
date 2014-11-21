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

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Mtf\Fixture\FixtureFactory;
use Mtf\ObjectManager;
use Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Product.
 *
 * Steps:
 * 1. Log in to Backend.
 * 2. Navigate to Products>Catalog.
 * 3. Open product created in preconditions.
 * 4. Click add new attribute.
 * 5. Fill out fields data according to data set.
 * 6. Save Product Attribute.
 * 7. Fill attribute value.
 * 8. Save product.
 * 7. Perform appropriate assertions.
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-30528
 */
class CreateProductAttributeEntityFromProductPageTest extends Scenario
{
    /**
     * CatalogProductAttribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Prepare data for test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['dataSet' => 'product_with_category_with_anchor']
        );
        $product->persist();
        return ['product' => $product];
    }

    /**
     * Run CreateProductAttributeEntity from product page test.
     *
     * @param CatalogProductAttribute $attribute
     * @return void
     */
    public function test(CatalogProductAttribute $attribute)
    {
        $this->attribute = $attribute;
        $this->executeScenario();
    }

    /**
     * Delete attribute after test.
     *
     * @return void
     */
    public function tearDown()
    {
        ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\TestStep\DeleteAttributeStep',
            ['attribute' => $this->attribute]
        )->run();
    }
}
