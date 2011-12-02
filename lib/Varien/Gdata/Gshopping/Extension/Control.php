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
 * @category    Varien
 * @package     Varien_Gdata
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension for <app:control> element. Adds destination logic to the parent.
 *
 * @category    Varien
 * @package     Varien_Gdata
 */
class Varien_Gdata_Gshopping_Extension_Control extends Zend_Gdata_App_Extension_Control
{
    /**
     * Constanst for destination mode excluded
     * @see http://code.google.com/intl/ru/apis/shopping/content/getting-started/requirements-products.html#destinations
     */
    const DEST_MODE_EXCLUDED = 2;

    /**
     * Constanst for destination mode required
     * @see http://code.google.com/intl/ru/apis/shopping/content/getting-started/requirements-products.html#destinations
     */
    const DEST_MODE_REQUIRED = 1;

    /**
     * Constanst for destination mode default
     * @see http://code.google.com/intl/ru/apis/shopping/content/getting-started/requirements-products.html#destinations
     */
    const DEST_MODE_DEFAULT = 0;

    /**
     * Mapping destinations to their modes (name => DEST_MODE_*)
     * @var array
     */
    protected $_destinations;

    /**
     * Create instance of class
     *
     * @param array $destinations Map destination's names to mode (DEST_MODE_*)
     * @param Zend_Gdata_App_Extension_Draft|null $draft Draft extension
     */
    public function __construct(array $destinations = array(), $draft = null)
    {
        $this->registerAllNamespaces(Varien_Gdata_Gshopping_Content::$namespaces);
        parent::__construct($draft);
        $this->_destinations = $destinations;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_destinations as $destName => $mode) {
            switch ($mode) {
                case self::DEST_MODE_EXCLUDED:
                    $elementName = 'excluded_destination';
                    break;
                case self::DEST_MODE_REQUIRED:
                    $elementName = 'required_destination';
                    break;
                default:
                    continue 2;
            }

            $dest = $element->ownerDocument->createElementNS($this->lookupNamespace('sc'), $elementName);
            $dest->setAttribute('dest', $destName);
            $element->appendChild($dest);
        }

        return $element;
    }

    /**
     * Given a child DOMNode, tries to determine how to map the data into
     * object instance members.  If no mapping is defined, Extension_Element
     * objects are created and stored in an array.
     *
     * @param DOMNode $child The DOMNode needed to be handled
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('sc') . ':' . 'excluded_destination':
                $this->_destinations[$child->getAttribute('dest')] = self::DEST_MODE_EXCLUDED;
                break;
            case $this->lookupNamespace('sc') . ':' . 'required_destination':
                $this->_destinations[$child->getAttribute('dest')] = self::DEST_MODE_REQUIRED;
                break;
            default:
                parent::takeChildFromDOM($child);
        }
    }

    /**
     * Returns map of destination name to DEST_MODE_* constants
     *
     * @return array
     */
    public function getDestinationsMode()
    {
        return $this->_destinations;
    }
}
