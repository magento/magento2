<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Handler\CatalogProductSimple;

use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Ui as AbstractUi;

/**
 * Class CreateProduct
 * Create a product
 */
class Ui extends AbstractUi implements CatalogProductSimpleInterface
{
    /**
     * Create product
     *
     * @param FixtureInterface $fixture [optional]
     * @return mixed|string
     */
    public function persist(FixtureInterface $fixture = null)
    {
        Factory::getApp()->magentoBackendLoginUser();

        $createProductPage = Factory::getPageFactory()->getCatalogProductNew();
        $createProductPage->open([
                'type' => $fixture->getDataConfig()['create_url_params']['type'],
                'set' => $fixture->getDataConfig()['create_url_params']['set'],
            ]);

        $createProductPage->getProductForm()->fill($fixture);
        $createProductPage->getFormPageActions()->save();
        $createProductPage->getMessagesBlock()->waitSuccessMessage();
    }
}
