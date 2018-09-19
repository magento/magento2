<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\ProductAttribute;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetAdd;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateAttributeSetEntity
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Attribute Set.
 * 3. Start to create new Attribute Set.
 * 4. Fill out fields data according to data set.
 * 5. Add created Product Attribute to Attribute Set.
 * 6. Save new Attribute Set.
 * 7. Verify created Attribute Set.
 *
 * @group Product_Attributes
 * @ZephyrId MAGETWO-25104
 */
class CreateAttributeSetEntityTest extends Injectable
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
     * Catalog Product Set add page
     *
     * @var CatalogProductSetAdd
     */
    protected $productSetAdd;

    /**
     * Catalog Product Set edit page
     *
     * @var CatalogProductSetEdit
     */
    protected $productSetEdit;

    /**
     * @param CatalogProductSetIndex $productSetIndex
     * @param CatalogProductSetAdd $productSetAdd
     * @param CatalogProductSetEdit $productSetEdit
     * @return void
     */
    public function __inject(
        CatalogProductSetIndex $productSetIndex,
        CatalogProductSetAdd $productSetAdd,
        CatalogProductSetEdit $productSetEdit
    ) {
        $this->productSetIndex = $productSetIndex;
        $this->productSetAdd = $productSetAdd;
        $this->productSetEdit = $productSetEdit;
    }

    /**
     * Run CreateAttributeSetEntity test
     *
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductAttribute $productAttribute
     * @return void
     */
    public function testCreateAttributeSet(
        CatalogAttributeSet $attributeSet,
        CatalogProductAttribute $productAttribute
    ) {
        $productAttribute->persist();

        //Steps
        $this->productSetIndex->open();
        $this->productSetIndex->getPageActionsBlock()->addNew();

        $this->productSetAdd->getAttributeSetForm()->fill($attributeSet);
        $this->productSetAdd->getPageActions()->save();
        $this->productSetEdit->getAttributeSetEditBlock()->moveAttribute($productAttribute->getData());
        $this->productSetEdit->getPageActions()->save();
    }
}
