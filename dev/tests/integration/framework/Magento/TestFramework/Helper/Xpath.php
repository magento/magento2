<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

use DOMDocument;
use DOMNodeList;
use DOMXPath;

/**
 * Xpath query helper
 */
class Xpath
{
    /**
     * Get elements count for XPath
     *
     * @param string $xpath
     * @param string $html
     * @return int
     */
    public static function getElementsCountForXpath($xpath, $html)
    {
        $nodes = self::getElementsForXpath((string) $xpath, (string) $html);
        return $nodes->length;
    }

    /**
     * Get elements for XPath
     *
     * @param string $xpath
     * @param string $html
     * @return DOMNodeList
     */
    public static function getElementsForXpath(string $xpath, string $html): DOMNodeList
    {
        $domXpath = self::getDOMXpath($html);
        return $domXpath->query($xpath);
    }

    /**
     * Get dom document instance
     *
     * @param string $html
     * @return DOMDocument
     */
    public static function getDOMDocument(string $html): DOMDocument
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($html);
        libxml_use_internal_errors(false);
        return $domDocument;
    }

    /**
     * Get dom xpath instance
     *
     * @param string $html
     * @return DOMXPath
     */
    public static function getDOMXpath(string $html): DOMXPath
    {
        return new DOMXPath(self::getDOMDocument($html));
    }
}
