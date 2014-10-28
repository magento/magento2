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

/**
 * Extension for handlilnd <sc:attribute> element
 *
 */
namespace Magento\Framework\Gdata\Gshopping\Extension;

class Attribute extends \Zend_Gdata_App_Extension_Element
{
    /**
     * Create a Content attribtue new instance.
     *
     * <sc:attribute name="[$name]" type="[$type]">
     *     [$text]
     * </sc:attribute>
     *
     * @param string $name The name of the Content attribute
     * @param string $text The text value of the Content attribute
     * @param string $type The type of the Content attribute
     * @param string $unit Currency for prices
     */
    public function __construct($name = null, $text = null, $type = null, $unit = null)
    {
        $this->registerAllNamespaces(\Magento\Framework\Gdata\Gshopping\Content::$namespaces);
        $reserved = array('id', 'image_link', 'content_language', 'target_country', 'expiration_date', 'adult');
        if (null !== $unit) {
            $this->_extensionAttributes['unit'] = array('name' => 'unit', 'value' => $unit);
        }
        if (in_array($name, $reserved)) {
            $elementName = $name;
        } else {
            $elementName = 'attribute';
            if (null !== $name) {
                $this->_extensionAttributes['name'] = array('name' => 'name', 'value' => $name);
            }
            if (null !== $type) {
                $this->_extensionAttributes['type'] = array('name' => 'type', 'value' => $type);
            }
        }
        parent::__construct($elementName, 'sc', $this->lookupNamespace('sc'), $text);
    }

    /**
     * Get the name of the attribute
     *
     * @return string|null name The requested object.
     */
    public function getName()
    {
        if ($this->_rootElement != 'attribute') {
            return $this->_rootElement;
        }
        return isset(
            $this->_extensionAttributes['name']['value']
        ) ? $this->_extensionAttributes['name']['value'] : null;
    }

    /**
     * Get the currency for prices
     *
     * @return string|null attribute type The requested object.
     */
    public function getUnit()
    {
        return isset(
            $this->_extensionAttributes['unit']['value']
        ) ? $this->_extensionAttributes['unit']['value'] : null;
    }

    /**
     * Get the type of the attribute
     *
     * @return string|null attribute type The requested object.
     */
    public function getType()
    {
        return isset(
            $this->_extensionAttributes['type']['value']
        ) ? $this->_extensionAttributes['type']['value'] : null;
    }

    /**
     * Set the currency for prices
     *
     * @param string $value
     * @return $this
     */
    public function setUnit($value)
    {
        $this->_extensionAttributes['unit'] = array('name' => 'unit', 'value' => $value);

        return $this;
    }

    /**
     * Set the type of the attribute
     *
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        $this->_extensionAttributes['type'] = array('name' => 'type', 'value' => $value);

        return $this;
    }
}
