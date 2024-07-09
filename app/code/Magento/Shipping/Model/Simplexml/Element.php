<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Simplexml;

/**
 * Extends SimpleXML to add valuable functionality to \SimpleXMLElement class
 *
 */
class Element extends \Magento\Framework\Simplexml\Element
{
    /**
     * Adds an attribute to the SimpleXML element
     *
     * @param string $name The name of the attribute to add.
     * @param string $value If specified, the value of the attribute.
     * @param string $namespace If specified, the namespace to which the attribute belongs.
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function addAttribute($name, $value = null, $namespace = null)
    {
        $value = $value !== null ? $this->xmlentities($value) : '';
        parent::addAttribute($name, $value, $namespace);
    }

    /**
     * Adds a child element to the XML node
     *
     * @param string $name The name of the child element to add.
     * @param string $value If specified, the value of the child element.
     * @param string $namespace If specified, the namespace to which the child element belongs.
     * @return \Magento\Shipping\Model\Simplexml\Element
     */
    #[\ReturnTypeWillChange]
    public function addChild($name, $value = null, $namespace = null)
    {
        if ($value !== null) {
            $value = $this->xmlentities($value);
        }
        return parent::addChild($name, $value, $namespace);
    }

    /**
     * Converts meaningful xml characters to xml entities
     *
     * @param string|null $value
     * @return string
     */
    public function xmlentities($value = null)
    {
        if ($value === null) {
            return '';
        }

        $value = str_replace('&amp;', '&', $value);
        $value = str_replace('&', '&amp;', $value);

        return $value;
    }
}
