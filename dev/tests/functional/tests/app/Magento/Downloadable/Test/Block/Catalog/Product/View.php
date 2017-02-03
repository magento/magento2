<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Catalog\Product;

use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class View
 * Downloadable product view block on the product page
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Block Downloadable links
     *
     * @var string
     */
    protected $blockDownloadableLinks = '//div[contains(@class,"field downloads")]';

    /**
     * Block Downloadable samples
     *
     * @var string
     */
    protected $blockDownloadableSamples = '.items.samples';

    /**
     * Get downloadable link block
     *
     * @return \Magento\Downloadable\Test\Block\Catalog\Product\View\Links
     */
    public function getDownloadableLinksBlock()
    {
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Catalog\Product\View\Links',
            [
                'element' => $this->_rootElement->find($this->blockDownloadableLinks, Locator::SELECTOR_XPATH)
            ]
        );
    }

    /**
     * Get downloadable samples block
     *
     * @return \Magento\Downloadable\Test\Block\Catalog\Product\View\Samples
     */
    public function getDownloadableSamplesBlock()
    {
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Catalog\Product\View\Samples',
            [
                'element' => $this->_rootElement->find($this->blockDownloadableSamples)
            ]
        );
    }

    /**
     * Fill specified option for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        /** @var DownloadableProduct $product */
        $productData = $product->getData();
        $downloadableLinks = isset($productData['downloadable_links']['downloadable']['link'])
            ? $productData['downloadable_links']['downloadable']['link']
            : [];
        $checkoutData = $product->getCheckoutData();
        if (isset($checkoutData['options'])) {
            // Replace link key to label
            foreach ($checkoutData['options']['links'] as $key => $linkData) {
                $linkKey = str_replace('link_', '', $linkData['label']);

                $linkData['label'] = isset($downloadableLinks[$linkKey]['title'])
                    ? $downloadableLinks[$linkKey]['title']
                    : $linkData['label'];

                $checkoutData['options']['links'][$key] = $linkData;
            }
            $this->getDownloadableLinksBlock()->fill($checkoutData['options']['links']);
        }
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {
        $downloadableOptions = [];

        if ($this->_rootElement->find($this->blockDownloadableLinks, Locator::SELECTOR_XPATH)->isVisible()) {
            $downloadableOptions['downloadable_links'] = [
                'title' => $this->getDownloadableLinksBlock()->getTitle(),
                'downloadable' => [
                    'link' => $this->getDownloadableLinksBlock()->getLinks(),
                ],
            ];
        }
        if ($this->_rootElement->find($this->blockDownloadableSamples)->isVisible()) {
            $downloadableOptions['downloadable_sample'] = [
                'title' => $this->getDownloadableSamplesBlock()->getTitle(),
                'downloadable' => [
                    'sample' => $this->getDownloadableSamplesBlock()->getLinks(),
                ],
            ];
        }

        return ['downloadable_options' => $downloadableOptions] + parent::getOptions($product);
    }
}
