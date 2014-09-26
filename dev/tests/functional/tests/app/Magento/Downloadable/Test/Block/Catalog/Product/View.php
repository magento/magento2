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

namespace Magento\Downloadable\Test\Block\Catalog\Product;

use Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

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
        /** @var DownloadableProductInjectable $product */
        $productData = $product->getData();
        $downloadableLinks = isset($productData['downloadable_links']['downloadable']['link'])
            ? $productData['downloadable_links']['downloadable']['link']
            : [];
        $data = $product->getCheckoutData()['options'];

        // Replace link key to label
        foreach ($data['links'] as $key => $linkData) {
            $linkKey = str_replace('link_', '', $linkData['label']);

            $linkData['label'] = isset($downloadableLinks[$linkKey]['title'])
                ? $downloadableLinks[$linkKey]['title']
                : $linkData['label'];

            $data['links'][$key] = $linkData;
        }

        $this->getDownloadableLinksBlock()->fill($data['links']);
        parent::fillOptions($product);
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
                    'link' => $this->getDownloadableLinksBlock()->getLinks()
                ]
            ];
        }
        if ($this->_rootElement->find($this->blockDownloadableSamples)->isVisible()) {
            $downloadableOptions['downloadable_sample'] = [
                'title' => $this->getDownloadableSamplesBlock()->getTitle(),
                'downloadable' => [
                    'sample' => $this->getDownloadableSamplesBlock()->getLinks()
                ]
            ];
        }

        return ['downloadable_options' => $downloadableOptions] + parent::getOptions($product);
    }
}
