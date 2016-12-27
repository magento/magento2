<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\Cart;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * Return prepared dataset.
     *
     * @param null|string $key
     * @return array
     */
    public function getData($key = null)
    {
        parent::getData($key);
        $checkoutDownloadableOptions = [];
        $checkoutData = $this->product->getCheckoutData();
        $downloadableOptions = $this->product->getDownloadableLinks();
        foreach ($checkoutData['options']['links'] as $link) {
            $keyLink = str_replace('link_', '', $link['label']);
            $checkoutDownloadableOptions[] = [
                'title' => $downloadableOptions['title'],
                'value' => $downloadableOptions['downloadable']['link'][$keyLink]['title'],
            ];
        }

        $this->data['options'] += $checkoutDownloadableOptions;

        return $this->data;
    }
}
