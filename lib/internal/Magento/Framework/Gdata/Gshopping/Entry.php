<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Google Shopping item model
 *
 */
namespace Magento\Framework\Gdata\Gshopping;

use Magento\Framework\Gdata\Gshopping\Extension\Attribute;

class Entry extends \Zend_Gdata_Entry
{
    /**
     * Name of the base class for Google Shopping entries
     *
     * var @string
     */
    protected $_entryClassName = 'Magento\Framework\Gdata\Gshopping\Entry';

    /**
     * Google Shopping attribute elements in the 'sc' and 'scp' namespaces
     *
     * @var string[]
     */
    protected $_contentAttributes = [];

    /**
     * Tax element extension
     *
     * @var array of \Magento\Framework\Gdata\Gshopping\Extension\Tax
     */
    protected $_tax = [];

    /**
     * Constructs a new \Magento\Framework\Gdata\Gshopping\Entry object.
     *
     * @param \DOMElement $element The \DOMElement on which to base this object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(\Magento\Framework\Gdata\Gshopping\Content::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a \DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param \DOMDocument $doc The \DOMDocument used to construct \DOMElements
     * @param int $majorVersion
     * @param int $minorVersion
     * @return \DOMElement The \DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_contentAttributes as $contentAttribute) {
            $element->appendChild($contentAttribute->getDOM($element->ownerDocument));
        }
        foreach ($this->_tax as $tax) {
            if ($tax instanceof \Magento\Framework\Gdata\Gshopping\Extension\Tax) {
                $element->appendChild($tax->getDOM($element->ownerDocument));
            }
        }

        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param \DOMNode $child The \DOMNode to process
     * @return void
     */
    protected function takeChildFromDOM($child)
    {
        $sc = $this->lookupNamespace('sc');
        $scp = $this->lookupNamespace('scp');
        $id = $child->namespaceURI . ':' . $child->localName;
        if ($child->localName == 'group') {
            $id .= ':' . $child->getAttribute('name');
        }

        switch ($id) {
            case "{$sc}:id":
            case "{$sc}:image_link":
            case "{$sc}:content_language":
            case "{$sc}:target_country":
            case "{$sc}:expiration_date":
            case "{$sc}:adult":
            case "{$sc}:attribute":
                $contentAttribute = new Attribute();
                $contentAttribute->transferFromDOM($child);
                $this->_contentAttributes[] = $contentAttribute;
                break;

            case "{$sc}:group:tax":
            case "{$scp}:tax":
                $tax = new \Magento\Framework\Gdata\Gshopping\Extension\Tax();
                $tax->transferFromDOM($child);
                $this->_tax[] = $tax;
                break;

            case $this->lookupNamespace('app') . ':' . 'control':
                $control = new \Magento\Framework\Gdata\Gshopping\Extension\Control();
                $control->transferFromDOM($child);
                $this->setControl($control);
                break;

            default:
                parent::takeChildFromDOM($child);
        }
    }

    /**
     * Adds a custom attribute to the entry in the following format:
     * <sc:attribute name="attribute_name" type="attribute_type">
     *     attribute_value
     * </sc:attribute>
     *
     * @param string $name The name of the attribute
     * @param string $text The text value of the attribute
     * @param string $type (optional) The type of the attribute.
     *          e.g.: 'text', 'number', 'float'
     * @param string $unit Currecnty for price
     * @return $this Provides a fluent interface
     */
    public function addContentAttribute($name, $text, $type = null, $unit = null)
    {
        $this->_contentAttributes[] = new Attribute($name, $text, $type, $unit);
        return $this;
    }

    /**
     * Removes a Content attribute from the current list of Base attributes
     *
     * @param string $name The attribute to be removed
     * @return $this Provides a fluent interface
     */
    public function removeContentAttribute($name)
    {
        foreach ($this->_contentAttributes as $key => $attribute) {
            if ($this->_normalizeName($attribute->getName()) == $this->_normalizeName($name)) {
                unset($this->_contentAttributes[$key]);
            }
        }

        return $this;
    }

    /**
     * Uploads changes in this entry to the server using \Zend_Gdata_App
     *
     * @param boolean $dryRun Whether the transaction is dry run or not.
     * @param string|null $uri The URI to send requests to, or null if $data
     *        contains the URI.
     * @param string|null $className The name of the class that should we
     *        deserializing the server response. If null, then
     *        'Zend_Gdata_App_Entry' will be used.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return \Zend_Gdata_App_Entry The updated entry
     * @throws \Zend_Gdata_App_InvalidArgumentException
     */
    public function save($dryRun = false, $uri = null, $className = null, $extraHeaders = [])
    {
        if ($dryRun) {
            $editLink = $this->getEditLink();
            if ($uri == null && $editLink !== null) {
                $uri = $editLink->getHref() . '?dry-run=true';
            }
            if ($uri === null) {
                throw new \Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
        }
        return parent::save($uri, $className, $extraHeaders);
    }

    /**
     * Deletes this entry to the server using the referenced
     * \Zend_Http_Client to do a HTTP DELETE to the edit link stored in this
     * entry's link collection.
     *
     * @param boolean $dryRun Whether the transaction is dry run or not
     * @return void
     * @throws \Zend_Gdata_App_InvalidArgumentException
     */
    public function delete($dryRun = false)
    {
        if ($dryRun) {
            $uri = null;
            $editLink = $this->getEditLink();
            if ($editLink !== null) {
                $uri = $editLink->getHref() . '?dry-run=true';
            }
            if ($uri === null) {
                throw new \Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
            $this->getService()->delete($uri);
        } else {
            parent::delete();
        }
    }

    /**
     * Return all the Content attributes
     * @return string[]
     */
    public function getContentAttributes()
    {
        return $this->_contentAttributes;
    }

    /**
     * Return an array of Content attributes that match the given attribute name
     *
     * @param string $name The name of the Content attribute to look for
     * @return string[] $matches Array of Attribute
     */
    public function getContentAttributesByName($name)
    {
        $matches = [];
        foreach ($this->_contentAttributes as $key => $attribute) {
            if ($this->_normalizeName($attribute->getName()) == $this->_normalizeName($name)) {
                $matches[] = $attribute;
            }
        }

        return $matches;
    }

    /**
     * Return content attribute that match the given attribute name by link
     * return null otherwise
     *
     * @param string $name The name of the Content attribute to look for
     * @return null|Attribute
     */
    public function getContentAttributeByName($name)
    {
        foreach ($this->_contentAttributes as $attribute) {
            if ($this->_normalizeName($attribute->getName()) == $this->_normalizeName($name)) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Set destinations for entry
     *
     * @param  array $modes Array with destination names and their statuses.
     *            format: array(name => \Magento\Framework\Gdata\Gshopping\Extension\Control::DEST_MODE_*),
     *            for instance: array('ProductSearch' => 2)
     * @return $this
     */
    public function setDestinationsMode(array $modes)
    {
        $this->setControl(new \Magento\Framework\Gdata\Gshopping\Extension\Control($modes));
        return $this;
    }

    /**
     * Retrieve destinations from entry.
     *
     * @return array
     */
    public function getDestinationsMode()
    {
        $control = $this->getControl();
        return $control instanceof
            \Magento\Framework\Gdata\Gshopping\Extension\Control ? $control->getDestinationsMode() : [];
    }

    /**
     * Add tax information to entry.
     *
     * @param array $taxInfo Array with tax's information,
     *           it may contains fields: tax_rate, tax_country, tax_region.
     * @return $this
     */
    public function addTax(array $taxInfo)
    {
        $this->_tax[] = new \Magento\Framework\Gdata\Gshopping\Extension\Tax($taxInfo);
        return $this;
    }

    /**
     * Get all taxes from entry
     *
     * @return array
     */
    public function getTaxes()
    {
        return $this->_tax;
    }

    /**
     * Clean taxes information.
     *
     * @return $this
     */
    public function cleanTaxes()
    {
        $this->_tax = [];
        return $this;
    }

    /**
     * Normalize attribute's name.
     * The name has to be in lower case and the words are separated by symbol "_".
     * For instance: Meta Description = meta_description
     *
     * @param string $name
     * @return string
     */
    protected function _normalizeName($name)
    {
        return strtolower(preg_replace('/[\s_]+/', '_', $name));
    }
}
