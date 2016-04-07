<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Element;

/**
 * Product additional information block on the product page.
 */
class Additional extends Block
{
    /**
     * Custom attribute selector.
     *
     * @var string
     */
    protected $attributeSelector = '//tr/th';

    /**
     * Custom attribute value selector.
     *
     * @var string
     */
    protected $attributeValueSelector = '/following::td[1]';

    /**
     * Get product attributes.
     *
     * @return SimpleElement[]
     */
    protected function getProductAttributes()
    {
        $data = [];
        $elements = $this->_rootElement->getElements($this->attributeSelector, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $data[$element->getText()] = $this->_rootElement->find(
                $this->attributeSelector . $this->attributeValueSelector,
                Locator::SELECTOR_XPATH
            );
        }

        return $data;
    }

    /**
     * Check if attribute value contains tag.
     *
     * @param CatalogProductAttribute $attribute
     * @return bool
     */
    public function hasHtmlTagInAttributeValue(CatalogProductAttribute $attribute)
    {
        $data = $attribute->getData();
        $defaultValue = preg_grep('/^default_value/', array_keys($data));
        $selector = $this->resolveHtmlStructure($data[array_shift($defaultValue)]);
        $element = $this->getProductAttributes()[$attribute->getFrontendLabel()];

        return $this->checkHtmlTagStructure($element, $selector)->isVisible();
    }

    /**
     * Find <tag1><tag2><tagN> ... </tagN></tag2></tag1> tag structure in element.
     *
     * @param SimpleElement $element
     * @param string $selector
     * @return SimpleElement
     */
    protected function checkHtmlTagStructure(SimpleElement $element, $selector)
    {
        return $element->find($selector);
    }

    /**
     * Get list of available attributes.
     *
     * @return array
     */
    public function getAttributeLabels()
    {
        return array_keys($this->getProductAttributes());
    }

    /**
     * Resolve html structure from given string, which contains html tags.
     *
     * @param string $stringWithHtml
     * @return array
     */
    protected function resolveHtmlStructure($stringWithHtml)
    {
        $selector = '';
        $dom = new \DOMDocument();
        $dom->loadHTML($stringWithHtml);
        $xmlStructure = $xmlStructure = $dom->saveXML();
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $xmlStructure, $htmlData);
        $htmlData = array_slice($htmlData, 2, -2); //Remove <html> and <body> tags
        $middleElement = ceil(count($htmlData) / 2);
        for ($index = 0; $index < $middleElement; $index++) {
            $selector .= $htmlData[$index]['tag'] . " ";
        }

        return trim($selector);
    }
}
