<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestCase;

use Mtf\Factory\Factory;

/**
 * Class EditConfigurableTest
 * Edit Configurable product
 */
class EditConfigurableTest extends CreateConfigurableTest
{
    /**
     * Edit configurable product and add new options to attribute
     *
     * @ZephyrId MAGETWO-12840
     * @return void
     */
    public function testCreateConfigurableProduct()
    {
        //Preconditions
        //Preparing Data for original product
        $configurable = Factory::getFixtureFactory()->getMagentoConfigurableProductConfigurableProduct();
        $configurable->switchData('configurable');
        $configurable->persist();
        $productSku = $configurable->getSku();
        //Preparing Data for editing product
        $editProduct = $configurable->getEditData();

        //Steps
        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $productForm = $createProductPage->getProductForm();
        //Login
        Factory::getApp()->magentoBackendLoginUser();
        $productGridPage = Factory::getPageFactory()->getCatalogProductIndex();
        $productGridPage->open();
        //Search and open original configurable product
        $productGridPage->getProductGrid()->searchAndOpen(['sku' => $productSku]);
        //Editing product options
        $productForm->fill($editProduct);
        $createProductPage->getFormPageActions()->save();
        //Verifying
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($editProduct);
        $this->assertOnFrontend($editProduct);
    }
}
