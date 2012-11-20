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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_Converter
{
    /**
     * Map of single=>plural sub-node names per node
     *
     * E.G. first element makes all 'tab' nodes be renamed to 'tabs' in system node.
     *
     * @var array
     */
    protected $nameMap = array(
        'system' => array('tab' => 'tabs', 'section' => 'sections'),
        'section' => array('group' => 'groups'),
        'group' => array('field' => 'fields'),
        'depends' => array('field' => 'fields'),
    );

    /**
     * Retrieve DOMDocument as array
     *
     * @param DOMNode $root
     * @return mixed
     */
    public function convert(DOMNode $root)
    {
        $result = $this->_processAttributes($root);

        $children = $root->childNodes;

        $processedSubLists = array();
        for ($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            $childName = $child->nodeName;
            $convertedChild = array();

            switch ($child->nodeType) {
                case XML_COMMENT_NODE:
                    continue 2;
                    break;

                case XML_TEXT_NODE:
                    if ($children->length && trim($child->nodeValue, "\n ") === '') {
                        continue 2;
                    }
                    $childName = 'value';
                    $convertedChild = $child->nodeValue;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $childName = 'value';
                    $convertedChild = $child->nodeValue;
                    break;

                default:
                    /** @var $child DOMElement */
                    if ($childName == 'attribute') {
                        $childName = $child->getAttribute('type');
                    }
                    $convertedChild = $this->convert($child);
                    break;
            }

            if (array_key_exists($root->nodeName, $this->nameMap)
                && array_key_exists($child->nodeName, $this->nameMap[$root->nodeName])) {
                $childName = $this->nameMap[$root->nodeName][$child->nodeName];
                $processedSubLists[] = $childName;
            }

            if (in_array($childName, $processedSubLists)) {
                if (is_array($convertedChild) && array_key_exists('id', $convertedChild)) {
                    $result[$childName][$convertedChild['id']] = $convertedChild;
                } else {
                    $result[$childName][] = $convertedChild;
                }
            } else if (array_key_exists($childName, $result)) {
                $result[$childName] = array($result[$childName], $convertedChild);
                $processedSubLists[] = $childName;
            } else {
                $result[$childName] = $convertedChild;
            }
        }

        if (count($result) == 1 && array_key_exists('value', $result)) {
            $result = $result['value'];
        }

        return $result;
    }

    /**
     * Process element attributes
     * 
     * @param DOMNode $root
     * @return array
     */
    protected function _processAttributes(DOMNode $root)
    {
        $result = array();

        if ($root->hasAttributes()) {
            $attributes = $root->attributes;
            foreach ($attributes as $attribute) {
                if ($root->nodeName == 'attribute' && $attribute->name == 'type') {
                    continue;
                }
                $result[$attribute->name] = $attribute->value;
            }
            return $result;
        }
        return $result;
    }
}
