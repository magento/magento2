<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Catalog\Product\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Links
 * Downloadable links blocks on frontend
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Links extends Block
{
    /**
     * Selector title for for links
     *
     * @var string
     */
    protected $title = '//div[contains(@class,"field downloads")]/label[@class="label"]/span';

    /**
     * Selector for link by label
     *
     * @var string
     */
    protected $linkByLabel = './/input[@type="checkbox" and (./../label/span[contains(text(),"%s")])]';

    /**
     * Choice link selector
     *
     * @var string
     */
    protected $choiceLink = './/*[contains(@class,"choice") and @data-role="link"]';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $separatelyForChoice = 'input[type="checkbox"]';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $linkForChoice = '[data-role="link"] label>span:first-child';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $sampleLinkForChoice = '.sample.link';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $priceForChoice = '.price-wrapper';

    /**
     * Checkbox selector item links
     *
     * @var string
     */
    protected $priceAdjustmentsForChoice = '.price-adjustments .price';

    /**
     * Get title for links block
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->find($this->title, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Fill links on product view page
     *
     * @param array $data
     * @return void
     */
    public function fill(array $data)
    {
        foreach ($data as $linkData) {
            $link = $this->_rootElement->find(
                sprintf($this->linkByLabel, $linkData['label']),
                Locator::SELECTOR_XPATH,
                'checkbox'
            );
            $link->setValue($linkData['value']);
        }
    }

    /**
     * Return links data on product page view
     *
     * @return array
     */
    public function getLinks()
    {
        $linksData = [];

        $choiceLinks = $this->_rootElement->getElements($this->choiceLink, Locator::SELECTOR_XPATH);
        foreach ($choiceLinks as $choiceLink) {
            $link = $choiceLink->find($this->linkForChoice);
            $sample = $choiceLink->find($this->sampleLinkForChoice);
            $price = $choiceLink->find($this->priceForChoice);
            $priceAdjustments = $choiceLink->find($this->priceAdjustmentsForChoice);

            $linkData = [
                'links_purchased_separately' => $choiceLink->find($this->separatelyForChoice)->isVisible()
                    ? 'Yes'
                    : 'No',
                'title' => $link->isVisible() ? $link->getText() : null,
                'sample' => $sample->isVisible() ? $sample->getText() : null,
                'price' => $price->isVisible() ? $this->escapePrice($price->getText()) : null,
                'price_adjustments' => $priceAdjustments->isVisible()
                    ? $this->escapePrice($priceAdjustments->getText())
                    : null,
            ];

            $linksData[] = array_filter($linkData);
        }

        return $linksData;
    }

    /**
     * Escape currency for price
     *
     * @param string $price
     * @return string
     */
    protected function escapePrice($price)
    {
        return preg_replace('/[^0-9\.,]/', '', $price);
    }
}
