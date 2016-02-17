<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Photos_Extension_Id
 */
#require_once 'Zend/Gdata/Photos/Extension/Id.php';

/**
 * @see Zend_Gdata_Photos_Extension_PhotoId
 */
#require_once 'Zend/Gdata/Photos/Extension/PhotoId.php';

/**
 * @see Zend_Gdata_Photos_Extension_Weight
 */
#require_once 'Zend/Gdata/Photos/Extension/Weight.php';

/**
 * @see Zend_Gdata_App_Extension_Category
 */
#require_once 'Zend/Gdata/App/Extension/Category.php';

/**
 * Data model class for a Comment Entry.
 *
 * To transfer user entries to and from the servers, including
 * creating new entries, refer to the service class,
 * Zend_Gdata_Photos.
 *
 * This class represents <atom:entry> in the Google Data protocol.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_CommentEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_CommentEntry';

    /**
     * gphoto:id element
     *
     * @var Zend_Gdata_Photos_Extension_Id
     */
    protected $_gphotoId = null;

    /**
     * gphoto:photoid element, differs from gphoto:id as this is an
     * actual identification number unique exclusively to photo entries,
     * whereas gphoto:id can refer to all gphoto objects
     *
     * @var Zend_Gdata_Photos_Extension_PhotoId
     */
    protected $_gphotoPhotoId = null;

    /**
     * Create a new instance.
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#comment',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
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
        if ($this->_gphotoId !== null) {
            $element->appendChild($this->_gphotoId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoPhotoId !== null) {
            $element->appendChild($this->_gphotoPhotoId->getDOM($element->ownerDocument));
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
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'id';
                $id = new Zend_Gdata_Photos_Extension_Id();
                $id->transferFromDOM($child);
                $this->_gphotoId = $id;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'photoid';
                $photoid = new Zend_Gdata_Photos_Extension_PhotoId();
                $photoid->transferFromDOM($child);
                $this->_gphotoPhotoId = $photoid;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * Get the value for this element's gphoto:photoid attribute.
     *
     * @see setGphotoPhotoId
     * @return string The requested attribute.
     */
    public function getGphotoPhotoId()
    {
        return $this->_gphotoPhotoId;
    }

    /**
     * Set the value for this element's gphoto:photoid attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_PhotoId The element being modified.
     */
    public function setGphotoPhotoId($value)
    {
        $this->_gphotoPhotoId = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:id attribute.
     *
     * @see setGphotoId
     * @return string The requested attribute.
     */
    public function getGphotoId()
    {
        return $this->_gphotoId;
    }

    /**
     * Set the value for this element's gphoto:id attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Id The element being modified.
     */
    public function setGphotoId($value)
    {
        $this->_gphotoId = $value;
        return $this;
    }
}
