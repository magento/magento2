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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function addAttribute($name, $value = null, $namespace = null)
    {
        if (!is_null($value)) {
            $value = $this->xmlentities($value);
        }
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
    public function addChild($name, $value = null, $namespace = null)
    {
        if (!is_null($value)) {
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
        $value = str_replace('&amp;', '&', $value);
        $value = str_replace('&', '&amp;', $value);
        return $value;
    }
}
