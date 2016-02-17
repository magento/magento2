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
 * @package    Zend_Pdf
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element.php';


/** Zend_Pdf_Destination */
#require_once 'Zend/Pdf/Destination.php';

/**
 * Abstract PDF explicit destination representation class
 *
 * @package    Zend_Pdf
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Destination_Explicit extends Zend_Pdf_Destination
{
    /**
     * Destination description array
     *
     * @var Zend_Pdf_Element_Array
     */
    protected $_destinationArray;

    /**
     * True if it's a remote destination
     *
     * @var boolean
     */
    protected $_isRemote;

    /**
     * Explicit destination object constructor
     *
     * @param Zend_Pdf_Element $destinationArray
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_Element $destinationArray)
    {
        if ($destinationArray->getType() != Zend_Pdf_Element::TYPE_ARRAY) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Explicit destination resource Array must be a direct or an indirect array object.');
        }

        $this->_destinationArray = $destinationArray;

        switch (count($this->_destinationArray->items)) {
            case 0:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Destination array must contain a page reference.');
                break;

            case 1:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Destination array must contain a destination type name.');
                break;

            default:
                // Do nothing
                break;
        }

        switch ($this->_destinationArray->items[0]->getType()) {
            case Zend_Pdf_Element::TYPE_NUMERIC:
                $this->_isRemote = true;
                break;

            case Zend_Pdf_Element::TYPE_DICTIONARY:
                $this->_isRemote = false;
                break;

            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Destination target must be a page number or page dictionary object.');
                break;
        }
    }

    /**
     * Returns true if it's a remote destination
     *
     * @return boolean
     */
    public function isRemote()
    {
        return $this->_isRemote;
    }

    /**
     * Get resource
     *
     * @internal
     * @return Zend_Pdf_Element
     */
    public function getResource()
    {
        return $this->_destinationArray;
    }
}
