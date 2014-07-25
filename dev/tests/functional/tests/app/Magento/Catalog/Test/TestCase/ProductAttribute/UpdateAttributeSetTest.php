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

use Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;

/**
 * Test Creation for UpdateAttributeSetTest
 *
 * Preconditions:
 * 1. An attribute is created
 * 2. An attribute template is created
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product Template.
 * 3. Open created Product Template.
 * 4. Click 'Add New' button to create new group
 * 5. Add created Product Attribute to created group.
 * 6. Fill out other fields data according to data set.
 * 7. Save Product Template.
 * 8. Preform all assertions.
 *
 * @group Product_Attributes_(CS)
 * @ZephyrId MAGETWO-26251
 */
class UpdateAttributeSetTest extends Injectable
{
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
     * Run UpdateProductTemplate test
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
