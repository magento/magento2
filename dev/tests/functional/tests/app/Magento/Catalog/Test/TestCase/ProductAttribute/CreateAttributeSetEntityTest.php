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
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetAdd;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;

/**
 * Test Creation for CreateAttributeSetEntity
 *
 * Test Flow:
 * 1. Log in to Backend.
 * 2. Navigate to Stores > Attributes > Product Template.
 * 3. Start to create new Product Template.
 * 4. Fill out fields data according to data set.
 * 5. Add created Product Attribute to Product Template.
 * 6. Save new Product Template.
 * 7. Verify created Product Template.
 *
 * @group Product_Attributes_(CS)
 * @ZephyrId MAGETWO-25104
 */
class CreateAttributeSetEntityTest extends Injectable
{
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
        $this->productSetEdit->getAttributeSetEditBlock()
            ->moveAttribute($productAttribute->getData(), 'Product Details');
        $this->productSetEdit->getPageActions()->save();
    }
}
