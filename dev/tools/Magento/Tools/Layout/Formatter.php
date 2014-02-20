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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Layout;

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
        $placeholders = array();
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
