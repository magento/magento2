<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml;

/**
 * Class \Magento\Framework\Xml\Generator
 *
 * @since 2.0.0
 */
class Generator
{
    /**
     * This value is used to replace numeric keys while formatting data for xml output.
     */
    const DEFAULT_ENTITY_ITEM_NAME = 'item';

    /**
     * @var \DOMDocument|null
     * @since 2.0.0
     */
    protected $_dom = null;

    /**
     * @var \DOMDocument
     * @since 2.0.0
     */
    protected $_currentDom;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_defaultIndexedArrayItemName;

    /**
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->_dom = new \DOMDocument('1.0');
        $this->_dom->formatOutput = true;
        $this->_currentDom = $this->_dom;
        return $this;
    }

    /**
     * @return \DOMDocument|null
     * @since 2.0.0
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @return \DOMDocument
     * @since 2.0.0
     */
    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    /**
     * @param \DOMDocument $node
     * @return $this
     * @since 2.0.0
     */
    protected function _setCurrentDom($node)
    {
        $this->_currentDom = $node;
        return $this;
    }

    /**
     * @param array $content
     * @return $this
     * @throws \DOMException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function arrayToXml($content)
    {
        $parentNode = $this->_getCurrentDom();
        if (!$content || !count($content)) {
            return $this;
        }
        foreach ($content as $_key => $_item) {
            $node = $this->getDom()->createElement(preg_replace('/[^\w-]/i', '', $_key));
            $parentNode->appendChild($node);
            if (is_array($_item) && isset($_item['_attribute'])) {
                if (is_array($_item['_value'])) {
                    if (isset($_item['_value'][0])) {
                        foreach ($_item['_value'] as $_v) {
                            $this->_setCurrentDom($node)->arrayToXml($_v);
                        }
                    } else {
                        $this->_setCurrentDom($node)->arrayToXml($_item['_value']);
                    }
                } else {
                    $child = $this->getDom()->createTextNode($_item['_value']);
                    $node->appendChild($child);
                }
                foreach ($_item['_attribute'] as $_attributeKey => $_attributeValue) {
                    $node->setAttribute($_attributeKey, $_attributeValue);
                }
            } elseif (is_string($_item)) {
                $text = $this->getDom()->createTextNode($_item);
                $node->appendChild($text);
            } elseif (is_array($_item) && !isset($_item[0])) {
                $this->_setCurrentDom($node)->arrayToXml($_item);
            } elseif (is_array($_item) && isset($_item[0])) {
                foreach ($_item as $v) {
                    $this->_setCurrentDom($node)->arrayToXml([$this->_getIndexedArrayItemName() => $v]);
                }
            }
        }
        return $this;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        return $this->getDom()->saveXML();
    }

    /**
     * @param string $file
     * @return $this
     * @since 2.0.0
     */
    public function save($file)
    {
        $this->getDom()->save($file);
        return $this;
    }

    /**
     * Set xml node name to use instead of numeric index during numeric arrays conversion.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setIndexedArrayItemName($name)
    {
        $this->_defaultIndexedArrayItemName = $name;
        return $this;
    }

    /**
     * Get xml node name to use instead of numeric index during numeric arrays conversion.
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getIndexedArrayItemName()
    {
        return isset($this->_defaultIndexedArrayItemName)
            ? $this->_defaultIndexedArrayItemName
            : self::DEFAULT_ENTITY_ITEM_NAME;
    }
}
