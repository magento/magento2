<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;

/**
 * Add metadata to the XMP template
 */
class AddXmpMetadata
{
    private const XMP_XPATH_SELECTOR_TITLE = '//dc:title/rdf:Alt/rdf:li';
    private const XMP_XPATH_SELECTOR_DESCRIPTION = '//dc:description/rdf:Alt/rdf:li';
    private const XMP_XPATH_SELECTOR_KEYWORDS = '//dc:subject/rdf:Bag';
    private const XMP_XPATH_SELECTOR_KEYWORDS_EACH = '//dc:subject/rdf:Bag/rdf:li';
    private const XMP_XPATH_SELECTOR_KEYWORD_ITEM = 'rdf:li';

    /**
     * Parse metadata
     *
     * @param string $data
     * @param MetadataInterface $metadata
     * @return string
     */
    public function execute(string $data, MetadataInterface $metadata): string
    {
        $xml = simplexml_load_string($data);
        $namespaces = $xml->getNamespaces(true);

        foreach ($namespaces as $prefix => $url) {
            $xml->registerXPathNamespace($prefix, $url);
        }

        if ($metadata->getTitle() === null) {
            $this->deleteValueByXpath($xml, self::XMP_XPATH_SELECTOR_TITLE);
        } else {
            $this->setValueByXpath($xml, self::XMP_XPATH_SELECTOR_TITLE, $metadata->getTitle());
        }
        if ($metadata->getDescription() === null) {
            $this->deleteValueByXpath($xml, self::XMP_XPATH_SELECTOR_DESCRIPTION);
        } else {
            $this->setValueByXpath($xml, self::XMP_XPATH_SELECTOR_DESCRIPTION, $metadata->getDescription());
        }
        if ($metadata->getKeywords() === null) {
            $this->deleteValueByXpath($xml, self::XMP_XPATH_SELECTOR_KEYWORDS);
        } else {
            $this->updateKeywords($xml, $metadata->getKeywords());
        }

        $data = $xml->asXML();
        return str_replace("<?xml version=\"1.0\"?>\n", '', $data);
    }

    /**
     * Update keywords
     *
     * @param \SimpleXMLElement $xml
     * @param array $keywords
     */
    private function updateKeywords(\SimpleXMLElement $xml, array $keywords): void
    {
        foreach ($xml->xpath(self::XMP_XPATH_SELECTOR_KEYWORDS_EACH) as $keywordElement) {
            unset($keywordElement[0]);
        }

        foreach ($xml->xpath(self::XMP_XPATH_SELECTOR_KEYWORDS) as $element) {
            foreach ($keywords as $keyword) {
                $element->addChild(self::XMP_XPATH_SELECTOR_KEYWORD_ITEM, $keyword);
            }
        }
    }

    /**
     * Deletes  xml node by xpath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xpath
     */
    private function deleteValueByXpath(\SimpleXMLElement $xml, string $xpath): void
    {
        foreach ($xml->xpath($xpath) as $element) {
            unset($element[0]);
        }
    }

    /**
     * Set value to xml node by xpath
     *
     * @param \SimpleXMLElement $xml
     * @param string $xpath
     * @param string $value
     */
    private function setValueByXpath(\SimpleXMLElement $xml, string $xpath, string $value): void
    {
        foreach ($xml->xpath($xpath) as $element) {
            $element[0] = $value;
        }
    }
}
