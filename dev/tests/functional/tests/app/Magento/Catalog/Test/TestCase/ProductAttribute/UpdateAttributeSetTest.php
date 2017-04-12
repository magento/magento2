<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateAttributeSetTest
 *
 * Preconditions:
 * 1. An attribute is created
 * 2. An attribute template is created
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Attribute Set.
 * 3. Open created Attribute Set.
 * 4. Click 'Add New' button to create new group
 * 5. Add created Product Attribute to created group.
 * 6. Fill out other fields data according to data set.
 * 7. Save Attribute Set.
 * 8. Preform all assertions.
 *
 * @group Product_Attributes
 * @ZephyrId MAGETWO-26251
 */
class UpdateAttributeSetTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Catalog Product Set page
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
     * Run UpdateAttributeSet test
     *
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogAttributeSet $attributeSetOriginal
     * @param CatalogProductAttribute $productAttributeOriginal
     * @return void
     */
    public function test(
        CatalogAttributeSet $attributeSet,
        CatalogAttributeSet $attributeSetOriginal,
        CatalogProductAttribute $productAttributeOriginal
    ) {
        // Precondition
        $attributeSetOriginal->persist();
        $productAttributeOriginal->persist();
        // Steps
        $filter = ['set_name' => $attributeSetOriginal->getAttributeSetName()];
        $this->productSetIndex->open();
        $this->productSetIndex->getGrid()->searchAndOpen($filter);
        $groupName = $attributeSet->getGroup();
        $this->productSetEdit->getAttributeSetEditBlock()->addAttributeSetGroup($groupName);
        $this->productSetEdit->getAttributeSetEditBlock()
            ->moveAttribute($productAttributeOriginal->getData(), $groupName);
        $this->productSetEdit->getAttributeSetEditForm()->fill($attributeSet);
        $this->productSetEdit->getPageActions()->save();
    }
}
