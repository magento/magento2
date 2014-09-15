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
        $productSku = $configurable->getProductSku();
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
        $createProductPage->getMessagesBlock()->assertSuccessMessage();
        //Flush cache
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushMagentoCache();
        //Verifying
        $this->assertOnGrid($editProduct);
        $this->assertOnFrontend($editProduct);
    }
}
