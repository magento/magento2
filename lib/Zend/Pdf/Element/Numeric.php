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
 * @version    $Id: Numeric.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Zend_Pdf_Element */
#require_once 'Zend/Pdf/Element.php';


/**
 * PDF file 'numeric' element implementation
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Element_Numeric extends Zend_Pdf_Element
{
    /**
     * Object value
     *
     * @var numeric
     */
    public $value;


    /**
     * Object constructor
     *
     * @param numeric $val
     * @throws Zend_Pdf_Exception
     */
    public function __construct($val)
    {
        if ( !is_numeric($val) ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Argument must be numeric');
        }

        $this->value = $val;
    }


    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_NUMERIC;
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        if (is_integer($this->value)) {
            return (string)$this->value;
        }

        /**
         * PDF doesn't support exponental format.
         * Fixed point format must be used instead
         */
        $prec = 0; $v = $this->value;
        while (abs( floor($v) - $v ) > 1e-10) {
            $prec++; $v *= 10;
        }
        return sprintf("%.{$prec}F", $this->value);
    }
}
