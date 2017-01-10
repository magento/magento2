<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Handler\GroupedProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Webapi as SimpleProductWebapi;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Create new grouped product via webapi.
 */
class Webapi extends SimpleProductWebapi implements GroupedProductInterface
{
    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $handlerCurl
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $handlerCurl
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport, $handlerCurl);
    }

    /**
     * Prepare data for creating product request.
     *
     * @return void
     */
    protected function prepareData()
    {
        parent::prepareData();
        $this->prepareProductLinks();
    }

    /**
     * Preparation of linked products.
     *
     * @return void
     */
    protected function prepareProductLinks()
    {
        $associatedData = $this->fixture->getAssociated();
        $productLinks = [];

        foreach ($associatedData['assigned_products'] as $key => $associatedProductData) {
            $product = $associatedData['products'][$key];
            $productConfig = $product->getDataConfig();

            $productLinks[] = [
                'sku' => $this->fixture->getSku(),
                'link_type' => 'associated',
                'linked_product_sku' => $product->getSku(),
                'linked_product_type' => $productConfig['type_id'],
                'position' => isset($associatedProductData['position']) ? $associatedProductData['position'] : 0,
                'extension_attributes' => [
                    'qty' => $associatedProductData['qty']
                ],
            ];
        }

        $this->fields['product']['product_links'] = $productLinks;
        unset($this->fields['links']);
    }
}
