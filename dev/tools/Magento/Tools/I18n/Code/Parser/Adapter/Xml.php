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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Parser\Adapter;

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
            return is_array($nodes) ? $nodes : array();
        }
        return array();
    }
}
