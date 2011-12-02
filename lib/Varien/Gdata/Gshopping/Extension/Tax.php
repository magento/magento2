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
 *
 *
 * @category    Varien
 * @package     Varien_Gdata
 */
class Varien_Gdata_Gshopping_Extension_Tax extends Zend_Gdata_App_Extension_Element
{
    /**
     * @var string The XML namespace prefix
     */
    protected $_rootNamespace = 'scp';

    /**
     * Key-value pair of tax information
     *
     * @var array
     */
    protected $_taxInfo;

    /**
     * Creates instance of class
     *
     * @param array $taxInfo as described in product requirements
     * @see http://code.google.com/intl/ru/apis/shopping/content/getting-started/requirements-products.html#tax
     */
    public function __construct(array $taxInfo = array())
    {
        $this->registerAllNamespaces(Varien_Gdata_Gshopping_Content::$namespaces);
        parent::__construct('tax', $this->_rootNamespace, $this->lookupNamespace($this->_rootNamespace));
        $this->_taxInfo = $taxInfo;
        foreach ($taxInfo as $key => $value) {
            $this->_extensionElements[] = new Zend_Gdata_App_Extension_Element(
                $key,
                $this->_rootNamespace,
                $this->_rootNamespaceURI,
                $value
            );
        }
    }

    /**
     * Magic getter to add access to _taxInfo data
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return isset($this->_taxInfo[$name]) ? $this->_taxInfo[$name] : parent::__get($name);
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
        if ($child->nodeType == XML_ELEMENT_NODE) {
            $name = ('attribute' == $child->localName) ? $child->getAttribute('name') : $child->localName;
            $this->_taxInfo[$name] = $child->textContent;
        }
        parent::takeChildFromDOM($child);
    }
}
