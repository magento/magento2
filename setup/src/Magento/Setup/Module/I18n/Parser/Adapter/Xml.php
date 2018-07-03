<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * Xml parser adapter
 *
 * Parse "translate" and 'translatable' node and collect phrases:
 * - from itself, it @translate or @translatable == true
 * - from given attributes, split by ",", " "
 */
class Xml extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function _parse()
    {
        foreach ($this->_getNodes($this->_file) as $element) {
            if (!$element instanceof \SimpleXMLElement) {
                continue;
            }
            $attributes = $element->attributes();
            if ((string)$attributes['translate'] === 'true' || (string)$attributes['translatable'] === 'true') {
                $this->_addPhrase((string)$element);
            } else {
                $this->parseTranslatableNodes($attributes, $element);
            }
        }
    }

    /**
     * Get nodes with translation
     *
     * @param string $file
     * @return array
     */
    protected function _getNodes($file)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);
        libxml_use_internal_errors(false);
        if ($xml) {
            $nodes = $xml->xpath('//*[@translate|@translatable]');
            unset($xml);

            return is_array($nodes) ? $nodes : [];
        }

        return [];
    }

    /**
     * Parse nodes pointed out in attribute "translate" and add phrases from them.
     *
     * @param \SimpleXMLElement $attributes
     * @param \SimpleXMLElement $element
     * @return void
     */
    private function parseTranslatableNodes(\SimpleXMLElement $attributes, \SimpleXMLElement $element)
    {
        $nodesDelimiter = strpos($attributes['translate'], ' ') === false ? ',' : ' ';
        foreach (explode($nodesDelimiter, $attributes['translate']) as $value) {
            $phrase = trim((string)$element->{$value});
            if ($phrase) {
                $this->_addPhrase($phrase);
            }
            $elementAttributes = $element->attributes();
            if (isset($elementAttributes[$value])) {
                $phrase = (string)$elementAttributes[$value];
                if ($phrase) {
                    $this->_addPhrase($phrase);
                }
            }
        }
    }
}
