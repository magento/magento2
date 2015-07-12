<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * Xml parser adapter
 *
 * Parse "translate" node and collect phrases:
 * - from itself, it @translate == true
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
            if ((string)$attributes['translate'] == 'true') {
                $this->_addPhrase((string)$element);
            } else {
                $nodesDelimiter = strpos($attributes['translate'], ' ') === false ? ',' : ' ';
                foreach (explode($nodesDelimiter, $attributes['translate']) as $value) {
                    $phrase = (string)$element->{$value};
                    if ($phrase) {
                        $this->_addPhrase($phrase);
                    }
                }
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
            $nodes = $xml->xpath('//*[@translate]');
            unset($xml);
            return is_array($nodes) ? $nodes : [];
        }
        return [];
    }
}
