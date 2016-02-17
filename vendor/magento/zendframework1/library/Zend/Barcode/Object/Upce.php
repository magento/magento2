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

/**
 * @see Zend_Barcode_Object_Ean13
 */
#require_once 'Zend/Barcode/Object/Ean13.php';

/**
 * @see Zend_Validate_Barcode
 */
#require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate UpcA barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Upce extends Zend_Barcode_Object_Ean13
{

    protected $_parities = array(
        0 => array(
            0 => array('B','B','B','A','A','A'),
            1 => array('B','B','A','B','A','A'),
            2 => array('B','B','A','A','B','A'),
            3 => array('B','B','A','A','A','B'),
            4 => array('B','A','B','B','A','A'),
            5 => array('B','A','A','B','B','A'),
            6 => array('B','A','A','A','B','B'),
            7 => array('B','A','B','A','B','A'),
            8 => array('B','A','B','A','A','B'),
            9 => array('B','A','A','B','A','B')),
        1 => array(
            0 => array('A','A','A','B','B','B'),
            1 => array('A','A','B','A','B','B'),
            2 => array('A','A','B','B','A','B'),
            3 => array('A','A','B','B','B','A'),
            4 => array('A','B','A','A','B','B'),
            5 => array('A','B','B','A','A','B'),
            6 => array('A','B','B','B','A','A'),
            7 => array('A','B','A','B','A','B'),
            8 => array('A','B','A','B','B','A'),
            9 => array('A','B','B','A','B','A'))
    );

    /**
     * Default options for Postnet barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 8;
        $this->_mandatoryChecksum = true;
        $this->_mandatoryQuietZones = true;
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        $text = parent::getText();
        if ($text{0} != 1) {
            $text{0} = 0;
        }
        return $text;
    }

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (3 * $this->_barThinWidth) * $this->_factor;
        $stopCharacter   = (6 * $this->_barThinWidth) * $this->_factor;
        $encodedData     = (7 * $this->_barThinWidth) * $this->_factor * 6;
        return $quietZone + $startCharacter + $encodedData + $stopCharacter + $quietZone;
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $barcodeTable = array();
        $height = ($this->_drawText) ? 1.1 : 1;

        // Start character (101)
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);

        $textTable = str_split($this->getText());
        $system = 0;
        if ($textTable[0] == 1) {
            $system = 1;
        }
        $checksum = $textTable[7];
        $parity = $this->_parities[$system][$checksum];

        for ($i = 1; $i < 7; $i++) {
            $bars = str_split($this->_codingMap[$parity[$i - 1]][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = array($b , $this->_barThinWidth , 0 , 1);
            }
        }

        // Stop character (10101)
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(0 , $this->_barThinWidth , 0 , $height);
        $barcodeTable[] = array(1 , $this->_barThinWidth , 0 , $height);
        return $barcodeTable;
    }

    /**
     * Partial function to draw text
     * @return void
     */
    protected function _drawText()
    {
        if ($this->_drawText) {
            $text = $this->getTextToDisplay();
            $characterWidth = (7 * $this->_barThinWidth) * $this->_factor;
            $leftPosition = $this->getQuietZone() - $characterWidth;
            for ($i = 0; $i < $this->_barcodeLength; $i ++) {
                $fontSize = $this->_fontSize;
                if ($i == 0 || $i == 7) {
                    $fontSize *= 0.8;
                }
                $this->_addText(
                    $text{$i},
                    $fontSize * $this->_factor,
                    $this->_rotate(
                        $leftPosition,
                        (int) $this->_withBorder * 2
                            + $this->_factor * ($this->_barHeight + $fontSize) + 1
                    ),
                    $this->_font,
                    $this->_foreColor,
                    'left',
                    - $this->_orientation
                );
                switch ($i) {
                    case 0:
                        $factor = 3;
                        break;
                    case 6:
                        $factor = 5;
                        break;
                    default:
                        $factor = 0;
                }
                $leftPosition = $leftPosition + $characterWidth + ($factor * $this->_barThinWidth * $this->_factor);
            }
        }
    }

    /**
     * Particular validation for Upce barcode objects
     * (to suppress checksum character substitution)
     *
     * @param string $value
     * @param array  $options
     * @throws Zend_Barcode_Object_Exception
     */
    protected function _validateText($value, $options = array())
    {
        $validator = new Zend_Validate_Barcode(array(
            'adapter'  => 'upce',
            'checksum' => false,
        ));

        $value = $this->_addLeadingZeros($value, true);

        if (!$validator->isValid($value)) {
            $message = implode("\n", $validator->getMessages());

            /**
             * @see Zend_Barcode_Object_Exception
             */
            #require_once 'Zend/Barcode/Object/Exception.php';
            throw new Zend_Barcode_Object_Exception($message);
        }
    }

    /**
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $text = $this->_addLeadingZeros($text, true);
        if ($text{0} != 1) {
            $text{0} = 0;
        }
        return parent::getChecksum($text);
    }
}
