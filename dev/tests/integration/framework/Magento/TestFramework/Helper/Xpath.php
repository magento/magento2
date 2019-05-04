<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Helper;

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
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($html);
        libxml_use_internal_errors(false);
        $domXpath = new \DOMXPath($domDocument);
        $nodes = $domXpath->query($xpath);
        return $nodes->length;
    }
}
