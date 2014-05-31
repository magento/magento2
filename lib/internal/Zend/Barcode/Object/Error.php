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
 * @package    Zend_Barcode
 * @subpackage Object
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Error.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** @see Zend_Barcode_Object_ObjectAbstract */
#require_once 'Zend/Barcode/Object/ObjectAbstract.php';

/**
 * Class for generate Barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Error extends Zend_Barcode_Object_ObjectAbstract
{
    /**
     * All texts are accepted
     * @param string $value
     * @return boolean
     */
    public function validateText($value)
    {
        return true;
    }

    /**
     * Height is forced
     * @return integer
     */
    public function getHeight($recalculate = false)
    {
        return 40;
    }

    /**
     * Width is forced
     * @return integer
     */
    public function getWidth($recalculate = false)
    {
        return 400;
    }

    /**
     * Reset precedent instructions
     * and draw the error message
     * @return array
     */
    public function draw()
    {
        $this->_instructions = array();
        $this->_addText('ERROR:', 10, array(5 , 18), $this->_font, 0, 'left');
        $this->_addText($this->_text, 10, array(5 , 32), $this->_font, 0, 'left');
        return $this->_instructions;
    }

    /**
     * For compatibility reason
     * @return void
     */
    protected function _prepareBarcode()
    {
    }

    /**
     * For compatibility reason
     * @return void
     */
    protected function _checkParams()
    {
    }

    /**
     * For compatibility reason
     * @return void
     */
    protected function _calculateBarcodeWidth()
    {
    }
}
