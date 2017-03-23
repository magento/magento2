<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\Tools;

class Formatter
{
    /**
     * @var string
     */
    private $_indent;

    /**
     * @param string $indent
     */
    public function __construct($indent = "    ")
    {
        $this->_indent = $indent;
    }

    /**
     * Return a well-formatted XML string
     *
     * @param string $xmlString
     * @return string
     */
    public function format($xmlString)
    {
        $xmlDom = new \DOMDocument('1.0');
        $xmlDom->formatOutput = true;
        $xmlDom->preserveWhiteSpace = false;
        $xmlDom->loadXML($xmlString);

        // replace text in the document with unique placeholders
        $placeholders = [];
        $xmlXpath = new \DOMXPath($xmlDom);
        /** @var DOMNode $textNode */
        foreach ($xmlXpath->query('//text() | //comment() | //@*') as $textNode) {
            $placeholder = \spl_object_hash($textNode);
            $placeholders[$placeholder] = $textNode->textContent;
            $textNode->nodeValue = $placeholder;
        }

        // render formatted XML structure
        $result = $xmlDom->saveXML();

        // replace the default 2-space indents
        $indent = $this->_indent;
        $result = \preg_replace_callback(
            '/^(?:\s{2})+/m',
            function (array $matches) use ($indent) {
                $indentCount = \strlen($matches[0]) >> 1;
                return \str_repeat($indent, $indentCount);
            },
            $result
        );

        // replace placeholders with values
        $result = \str_replace(\array_keys($placeholders), \array_values($placeholders), $result);

        return $result;
    }
}
