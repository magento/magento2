<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Persist listened schema to db_schema.xml file.
 */
class XmlPersistor
{
    /**
     * Persist XML object to file.
     *
     * @param \SimpleXMLElement $simpleXMLElement
     * @param $path
     */
    public function persist(\SimpleXMLElement $simpleXMLElement, $path)
    {
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($simpleXMLElement->asXML());
        file_put_contents(
            $path,
            str_replace(
                ' xmlns:xsi="xsi"', //replace namespace, as we do not need it for xsi namespace
                '',
                $dom->saveXML()
            )
        );
    }
}
