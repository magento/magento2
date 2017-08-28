<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;


use Magento\Catalog\Test\Constraint\AssertDateInvalidErrorMessage as AssertDateErrorMessage;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 *
 * 1. Login to backend.
 * 2. Create a product with invalid from and To dates
 * 3. Save the product which generates an error messsage
 * 4. Modify the  dates to valid values
 * 5. Save the product again
 * 6. Product is saved successfully
  */

class ReSavingProductAfterInitialSaveTest extends Injectable
{
    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;
    /**
     * Product page with a grid
     *
     * @var CatalogProductIndex
     */
    private $productGrid;

    /**
     * Page to create a product
     *
     * @var CatalogProductNew
     */
    private $newProductPage;

    /**
     * Assert Invalid Date error message.
     *
     * @var AssertDateErrorMessage
     */
    private $assertDateErrorMessage;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     */
   public function __inject
   (
       CatalogProductEdit $catalogProductEdit,
       CatalogProductIndex $productGrid,
       CatalogProductNew $newProductPage,
       FixtureFactory $fixtureFactory,
       AssertDateErrorMessage $assertDateErrorMessage
   )
   {
       $this->productGrid = $productGrid;
       $this->newProductPage = $newProductPage;
       $this->catalogProductEdit = $catalogProductEdit;
       $this->fixtureFactory = $fixtureFactory;
       $this->assertDateErrorMessage = $assertDateErrorMessage;

   }

    /**
     * Verify the product can be saved successfully after its initial save is failed.
     * @param CatalogProductSimple $originalProduct
     * @param CatalogProductSimple $productWithValidFromDate
     * @param CatalogProductSimple $productWithValidToDate
     */

   public function test(CatalogProductSimple $originalProduct, CatalogProductSimple $productWithValidFromDate, CatalogProductSimple $productWithValidToDate )
   {
       $this->productGrid->open();
       $this->productGrid->getGridPageActionBlock()->addProduct('simple');
       $this->newProductPage->getProductForm()->fill($originalProduct);
       $this->catalogProductEdit->getProductForm()->fill($productWithValidFromDate);
       $this->catalogProductEdit->getFormPageActions()->save();
       $this->assertDateErrorMessage->processAssert($this->catalogProductEdit);
       $this->catalogProductEdit->getProductForm()->fill($productWithValidToDate);
       $this->catalogProductEdit->getFormPageActions()->save();
   }
}