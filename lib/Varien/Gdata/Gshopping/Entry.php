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
 * Google Shopping item model
 *
 * @category    Varien
 * @package     Varien_Gdata
 */
class Varien_Gdata_Gshopping_Entry extends Zend_Gdata_Entry
{

    /**
     * Name of the base class for Google Shopping entries
     *
     * var @string
     */
    protected $_entryClassName = 'Varien_Gdata_Gshopping_Entry';

    /**
     * Google Shopping attribute elements in the 'sc' and 'scp' namespaces
     *
     * @var array
     */
    protected $_contentAttributes = array();

    /**
     * Tax element extension
     *
     * @var array of Varien_Gdata_Gshopping_Extension_Tax
     */
    protected $_tax = array();

    /**
     * Constructs a new Varien_Gdata_Gshopping_Entry object.
     * @param DOMElement $element The DOMElement on which to base this object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Varien_Gdata_Gshopping_Content::$namespaces);
        parent::__construct($element);
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_contentAttributes as $contentAttribute) {
            $element->appendChild($contentAttribute->getDOM($element->ownerDocument));
        }
        foreach ($this->_tax as $tax) {
            if ($tax instanceof Varien_Gdata_Gshopping_Extension_Tax) {
                $element->appendChild($tax->getDOM($element->ownerDocument));
            }
        }

        return $element;
    }


    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
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
            case "$sc:id":
            case "$sc:image_link":
            case "$sc:content_language":
            case "$sc:target_country":
            case "$sc:expiration_date":
            case "$sc:adult":
            case "$sc:attribute":
                $contentAttribute = new Varien_Gdata_Gshopping_Extension_Attribute();
                $contentAttribute->transferFromDOM($child);
                $this->_contentAttributes[] = $contentAttribute;
                break;

            case "$sc:group:tax":
            case "$scp:tax":
                $tax = new Varien_Gdata_Gshopping_Extension_Tax();
                $tax->transferFromDOM($child);
                $this->_tax[] = $tax;
                break;

            case $this->lookupNamespace('app') . ':' . 'control':
                $control = new Varien_Gdata_Gshopping_Extension_Control();
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
     * @param string $value The text value of the attribute
     * @param string $type (optional) The type of the attribute.
     *          e.g.: 'text', 'number', 'float'
     * @param string $unit Currecnty for price
     * @return Varien_Gdata_Gshopping_Entry Provides a fluent interface
     */
    public function addContentAttribute($name, $text, $type = null, $unit = null)
    {
        $this->_contentAttributes[] = new Varien_Gdata_Gshopping_Extension_Attribute($name, $text, $type, $unit);
        return $this;
    }

    /**
     * Removes a Content attribute from the current list of Base attributes
     *
     * @param Zend_Gdata_Gbase_Extension_BaseAttribute $baseAttribute The attribute to be removed
     * @return Zend_Gdata_Gbase_Entry Provides a fluent interface
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
     * Uploads changes in this entry to the server using Zend_Gdata_App
     *
     * @param boolean $dryRun Whether the transaction is dry run or not.
     * @param string|null $uri The URI to send requests to, or null if $data
     *        contains the URI.
     * @param string|null $className The name of the class that should we
     *        deserializing the server response. If null, then
     *        'Zend_Gdata_App_Entry' will be used.
     * @param array $extraHeaders Extra headers to add to the request, as an
     *        array of string-based key/value pairs.
     * @return Zend_Gdata_App_Entry The updated entry
     * @throws Zend_Gdata_App_Exception
     */
    public function save($dryRun = false, $uri = null, $className = null, $extraHeaders = array())
    {
        if ($dryRun) {
            $editLink = $this->getEditLink();
            if ($uri == null && $editLink !== null) {
                $uri = $editLink->getHref() . '?dry-run=true';
            }
            if ($uri === null) {
                throw new Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
        }
        return parent::save($uri, $className, $extraHeaders);
    }

    /**
     * Deletes this entry to the server using the referenced
     * Zend_Http_Client to do a HTTP DELETE to the edit link stored in this
     * entry's link collection.
     *
     * @param boolean $dryRun Whether the transaction is dry run or not
     * @return void
     * @throws Zend_Gdata_App_Exception
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
                throw new Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
            $this->getService()->delete($uri);
        } else {
            parent::delete();
        }
    }

    /**
     * Return all the Content attributes
     * @return array
     */
    public function getContentAttributes()
    {
        return $this->_contentAttributes;
    }

    /**
     * Return an array of Content attributes that match the given attribute name
     *
     * @param string $name The name of the Content attribute to look for
     * @return array $matches Array of Varien_Gdata_Gshopping_Extension_Attribute
     */
    public function getContentAttributesByName($name)
    {
        $matches = array();
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
     * @return null|Varien_Gdata_Gshopping_Extension_Attribute
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
     *            format: array(name => Varien_Gdata_Gshopping_Extension_Control::DEST_MODE_*),
     *            for instance: array('ProductSearch' => 2)
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function setDestinationsMode(array $modes)
    {
        $this->setControl(new Varien_Gdata_Gshopping_Extension_Control($modes));
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
        return ($control instanceof Varien_Gdata_Gshopping_Extension_Control)
            ? $control->getDestinationsMode()
            : array();
    }

    /**
     * Add tax information to entry.
     *
     * @param array $taxInfo Array with tax's information,
     *           it may contains fields: tax_rate, tax_country, tax_region.
     */
    public function addTax(array $taxInfo)
    {
        $this->_tax[] = new Varien_Gdata_Gshopping_Extension_Tax($taxInfo);
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
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function cleanTaxes()
    {
        $this->_tax = array();
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
