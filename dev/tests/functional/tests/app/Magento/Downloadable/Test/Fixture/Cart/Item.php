<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\Cart;

use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        parent::__construct($product);

        /** @var DownloadableProduct $product */
        $checkoutDownloadableOptions = [];
        $checkoutData = $product->getCheckoutData();
        $downloadableOptions = $product->getDownloadableLinks();
        foreach ($checkoutData['options']['links'] as $link) {
            $keyLink = str_replace('link_', '', $link['label']);
            $checkoutDownloadableOptions[] = [
                'title' => $downloadableOptions['title'],
                'value' => $downloadableOptions['downloadable']['link'][$keyLink]['title'],
            ];
        }

        $this->data['options'] += $checkoutDownloadableOptions;
    }
}
