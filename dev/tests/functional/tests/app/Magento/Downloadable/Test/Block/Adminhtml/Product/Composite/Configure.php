<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Adminhtml\Product\Composite;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml downloadable product composite configure block
 */
class Configure extends \Magento\Catalog\Test\Block\Adminhtml\Product\Composite\Configure
{
    /**
     * Fill options for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $data = $this->prepareData($product->getData());
        $this->_fill($data);
    }

    /**
     * Prepare data
     *
     * @param array $fields
     * @return array
     */
    protected function prepareData(array $fields)
    {
        $productOptions = [];
        $checkoutData = $fields['checkout_data']['options'];
        $productLinks = $fields['downloadable_links']['downloadable']['link'];

        if (!empty($checkoutData['links'])) {
            $linkMapping = $this->dataMapping(['link' => '']);
            $selector = $linkMapping['link']['selector'];
            foreach ($checkoutData['links'] as $key => $link) {
                $link['label'] = $productLinks[str_replace('link_', '', $link['label'])]['title'];
                $linkMapping['link']['selector'] = str_replace('%link_name%', $link['label'], $selector);
                $linkMapping['link']['value'] = $link['value'];
                $productOptions['link_' . $key] = $linkMapping['link'];
            }
        }

        return $productOptions;
    }
}
