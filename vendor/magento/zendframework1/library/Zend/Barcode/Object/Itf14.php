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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Barcode_Object_Code25interleaved */
#require_once 'Zend/Barcode/Object/Code25interleaved.php';

/** @see Zend_Validate_Barcode */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Itf14 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Itf14 extends Zend_Barcode_Object_Code25interleaved
{

    /**
     * Default options for Identcode barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 14;
        $this->_mandatoryChecksum = true;
    }
}
