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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf.php';


/** Zend_Pdf_Element */
#require_once 'Zend/Pdf/Element.php';

/**
 * PDF file 'stream' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Stream extends Zend_Pdf_Element
{
    /**
     * Object value
     *
     * @var Zend_Memory_Container
     */
    public $value;


    /**
     * Object constructor
     *
     * @param string $val
     */
    public function __construct($val)
    {
        $this->value = Zend_Pdf::getMemoryManager()->create($val);
    }


    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_STREAM;
    }


    /**
     * Stream length.
     * (Method is used to avoid string copying, which may occurs in some cases)
     *
     * @return integer
     */
    public function length()
    {
        return strlen($this->value->getRef());
    }


    /**
     * Clear stream
     *
     */
    public function clear()
    {
        $ref = &$this->value->getRef();
        $ref = '';
        $this->value->touch();
    }


    /**
     * Append value to a stream
     *
     * @param mixed $val
     */
    public function append($val)
    {
        $ref = &$this->value->getRef();
        $ref .= (string)$val;
        $this->value->touch();
    }


    /**
     * Detach PDF object from the factory (if applicable), clone it and attach to new factory.
     *
     * @param Zend_Pdf_ElementFactory $factory  The factory to attach
     * @param array &$processed  List of already processed indirect objects, used to avoid objects duplication
     * @param integer $mode  Cloning mode (defines filter for objects cloning)
     * @returns Zend_Pdf_Element
     */
    public function makeClone(Zend_Pdf_ElementFactory $factory, array &$processed, $mode)
    {
        return new self($this->value->getRef());
    }

    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        return "stream\n" . $this->value->getRef() . "\nendstream";
    }
}
