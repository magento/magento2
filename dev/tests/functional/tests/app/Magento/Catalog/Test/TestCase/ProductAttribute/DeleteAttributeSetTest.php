<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Delete Attribute Set (Attribute Set)
 *
 * Preconditions:
 * 1. An attribute is created.
 * 2. An attribute template is created.
 * 3. A simple product is created with this attribute and template
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Attribute Set.
 * 3. Open created Attribute Set.
 * 4. Click 'Delete' button.
 * 5. Perform all assertions.
 *
 * @group Product_Attributes_(MX)
 * @ZephyrId MAGETWO-25473
 */
class DeleteAttributeSetTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

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
     * @param CatalogAttributeSet $attributeSet
     * @return array
     */
    public function test(FixtureFactory $fixtureFactory, CatalogAttributeSet $attributeSet)
    {
        // Precondition
        $attributeSet->persist();
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $attributeSet],
                ],
            ]
        );
        $product->persist();

        // Steps
        $filter = ['set_name' => $attributeSet->getAttributeSetName()];
        $this->productSetIndex->open();
        $this->productSetIndex->getGrid()->searchAndOpen($filter);
        $this->productSetEdit->getPageActions()->delete();
        $this->productSetEdit->getModalBlock()->acceptAlert();

        return ['product' => $product];
    }
}
