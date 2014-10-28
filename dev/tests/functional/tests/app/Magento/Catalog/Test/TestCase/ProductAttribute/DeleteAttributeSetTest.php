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

use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;

/**
 * Test Creation for Delete Attribute Set (Product Template)
 *
 * Preconditions:
 * 1. An attribute is created.
 * 2. An attribute template is created.
 * 3. A simple product is created with this attribute and template
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product Template.
 * 3. Open created Product Template.
 * 4. Click 'Delete Attribute Set' button.
 * 5. Perform all assertions.
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-25473
 */
class DeleteAttributeSetTest extends Injectable
{
    /**
     * Catalog Product Set index page
     *
     * @var CatalogProductSetIndex
     */
    protected $productSetIndex;

    /**
     * Catalog Product Set edit page
     *
     * @var CatalogProductSetEdit
     */
    protected $productSetEdit;

    /**
     * Inject data
     *
     * @param CatalogProductSetIndex $productSetIndex
     * @param CatalogProductSetEdit $productSetEdit
     * @return void
     */
    public function __inject(
        CatalogProductSetIndex $productSetIndex,
        CatalogProductSetEdit $productSetEdit
    ) {
        $this->productSetIndex = $productSetIndex;
        $this->productSetEdit = $productSetEdit;
    }

    /**
     * Run DeleteAttributeSet test
     *
     * @param FixtureFactory $fixtureFactory
     * @param CatalogAttributeSet $productTemplate
     * @return array
     */
    public function test(FixtureFactory $fixtureFactory, CatalogAttributeSet $productTemplate)
    {
        // Precondition
        $productTemplate->persist();
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'default',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $productTemplate],
                ],
            ]
        );
        $product->persist();

        // Steps
        $filter = ['set_name' => $productTemplate->getAttributeSetName()];
        $this->productSetIndex->open();
        $this->productSetIndex->getGrid()->searchAndOpen($filter);
        $this->productSetEdit->getPageActions()->delete();

        return ['product' => $product];
    }
}
