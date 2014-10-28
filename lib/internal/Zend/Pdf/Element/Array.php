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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Array.php 22797 2010-08-06 15:02:12Z alexander $
 */


/** Zend_Pdf_Element */
#require_once 'Zend/Pdf/Element.php';


/**
 * PDF file 'array' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Array extends Zend_Pdf_Element
{
    /**
     * Array element items
     *
     * Array of Zend_Pdf_Element objects
     *
     * @var array
     */
    public $items;


    /**
     * Object constructor
     *
     * @param array $val   - array of Zend_Pdf_Element objects
     * @throws Zend_Pdf_Exception
     */
    public function __construct($val = null)
    {
        $this->items = new ArrayObject();

        if ($val !== null  &&  is_array($val)) {
            foreach ($val as $element) {
                if (!$element instanceof Zend_Pdf_Element) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception('Array elements must be Zend_Pdf_Element objects');
                }
                $this->items[] = $element;
            }
        } else if ($val !== null){
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Argument must be an array');
        }
    }


    /**
     * Getter
     *
     * @param string $property
     * @throws Zend_Pdf_Exception
     */
    public function __get($property) {
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Undefined property: Zend_Pdf_Element_Array::$' . $property);
    }


    /**
     * Setter
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws Zend_Pdf_Exception
     */
    public function __set($property, $value) {
        #require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Undefined property: Zend_Pdf_Element_Array::$' . $property);
    }

    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_ARRAY;
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        $outStr = '[';
        $lastNL = 0;

        foreach ($this->items as $element) {
            if (strlen($outStr) - $lastNL > 128)  {
                $outStr .= "\n";
                $lastNL = strlen($outStr);
            }

            $outStr .= $element->toString($factory) . ' ';
        }
        $outStr .= ']';

        return $outStr;
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
        $newArray = new self();

        foreach ($this->items as $key => $value) {
            $newArray->items[$key] = $value->makeClone($factory, $processed, $mode);
        }

        return $newArray;
    }

    /**
     * Set top level parent indirect object.
     *
     * @param Zend_Pdf_Element_Object $parent
     */
    public function setParentObject(Zend_Pdf_Element_Object $parent)
    {
        parent::setParentObject($parent);

        foreach ($this->items as $item) {
            $item->setParentObject($parent);
        }
    }

    /**
     * Convert PDF element to PHP type.
     *
     * Dictionary is returned as an associative array
     *
     * @return mixed
     */
    public function toPhp()
    {
        $phpArray = array();

        foreach ($this->items as $item) {
            $phpArray[] = $item->toPhp();
        }

        return $phpArray;
    }
}
