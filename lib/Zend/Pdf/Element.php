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
 * @version    $Id: Element.php 22797 2010-08-06 15:02:12Z alexander $
 */


/**
 * PDF file element implementation
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Element
{
    const TYPE_BOOL        = 1;
    const TYPE_NUMERIC     = 2;
    const TYPE_STRING      = 3;
    const TYPE_NAME        = 4;
    const TYPE_ARRAY       = 5;
    const TYPE_DICTIONARY  = 6;
    const TYPE_STREAM      = 7;
    const TYPE_NULL        = 11;

    /**
     * Reference to the top level indirect object, which contains this element.
     *
     * @var Zend_Pdf_Element_Object
     */
    private $_parentObject = null;

    /**
     * Return type of the element.
     * See ZPdfPDFConst for possible values
     *
     * @return integer
     */
    abstract public function getType();

    /**
     * Convert element to a string, which can be directly
     * written to a PDF file.
     *
     * $factory parameter defines operation context.
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    abstract public function toString($factory = null);

    const CLONE_MODE_SKIP_PAGES    = 1; // Do not follow pages during deep copy process
    const CLONE_MODE_FORCE_CLONING = 2; // Force top level object cloning even it's already processed

    /**
     * Detach PDF object from the factory (if applicable), clone it and attach to new factory.
     *
     * @todo It's nevessry to check if SplObjectStorage class works faster
     * (Needs PHP 5.3.x to attach object _with_ additional data to storage)
     *
     * @param Zend_Pdf_ElementFactory $factory  The factory to attach
     * @param array &$processed List of already processed indirect objects, used to avoid objects duplication
     * @param integer $mode  Cloning mode (defines filter for objects cloning)
     * @returns Zend_Pdf_Element
     */
    public function makeClone(Zend_Pdf_ElementFactory $factory, array &$processed, $mode)
    {
        return clone $this;
    }

    /**
     * Set top level parent indirect object.
     *
     * @param Zend_Pdf_Element_Object $parent
     */
    public function setParentObject(Zend_Pdf_Element_Object $parent)
    {
        $this->_parentObject = $parent;
    }


    /**
     * Get top level parent indirect object.
     *
     * @return Zend_Pdf_Element_Object
     */
    public function getParentObject()
    {
        return $this->_parentObject;
    }


    /**
     * Mark object as modified, to include it into new PDF file segment.
     *
     * We don't automate this action to keep control on PDF update process.
     * All new objects are treated as "modified" automatically.
     */
    public function touch()
    {
        if ($this->_parentObject !== null) {
            $this->_parentObject->touch();
        }
    }

    /**
     * Clean up resources, used by object
     */
    public function cleanUp()
    {
        // Do nothing
    }

    /**
     * Convert PDF element to PHP type.
     *
     * @return mixed
     */
    public function toPhp()
    {
        return $this->value;
    }

    /**
     * Convert PHP value into PDF element.
     *
     * @param mixed $input
     * @return Zend_Pdf_Element
     */
    public static function phpToPdf($input)
    {
        if (is_numeric($input)) {
            #require_once 'Zend/Pdf/Element/Numeric.php';
            return new Zend_Pdf_Element_Numeric($input);
        } else if (is_bool($input)) {
            #require_once 'Zend/Pdf/Element/Boolean.php';
            return new Zend_Pdf_Element_Boolean($input);
        } else if (is_array($input)) {
            $pdfElementsArray = array();
            $isDictionary = false;

            foreach ($input as $key => $value) {
                if (is_string($key)) {
                    $isDictionary = true;
                }
                $pdfElementsArray[$key] = Zend_Pdf_Element::phpToPdf($value);
            }

            if ($isDictionary) {
                #require_once 'Zend/Pdf/Element/Dictionary.php';
                return new Zend_Pdf_Element_Dictionary($pdfElementsArray);
            } else {
                #require_once 'Zend/Pdf/Element/Array.php';
                return new Zend_Pdf_Element_Array($pdfElementsArray);
            }
        } else {
            #require_once 'Zend/Pdf/Element/String.php';
            return new Zend_Pdf_Element_String((string)$input);
        }
    }
}
