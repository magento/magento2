<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Extension for <app:control> element. Adds destination logic to the parent.
 *
 */
namespace Magento\Framework\Gdata\Gshopping\Extension;

class Control extends \Zend_Gdata_App_Extension_Control
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
     * @param \Zend_Gdata_App_Extension_Draft|null $draft Draft extension
     */
    public function __construct(array $destinations = [], $draft = null)
    {
        $this->registerAllNamespaces(\Magento\Framework\Gdata\Gshopping\Content::$namespaces);
        parent::__construct($draft);
        $this->_destinations = $destinations;
    }

    /**
     * Retrieves a \DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param \DOMDocument|null $doc The \DOMDocument used to construct \DOMElements
     * @param int $majorVersion
     * @param null|int $minorVersion
     * @return \DOMElement The \DOMElement representing this element and all
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
     * Given a child \DOMNode, tries to determine how to map the data into
     * object instance members.  If no mapping is defined, Extension_Element
     * objects are created and stored in an array.
     *
     * @param \DOMNode $child The \DOMNode needed to be handled
     * @return void
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
