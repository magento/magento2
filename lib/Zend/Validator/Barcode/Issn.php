<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\Barcode;

/**
 * @category   Zend
 * @package    Zend_Validate
 */
class Issn extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(array(8, 13));
        $this->setCharacters('0123456789X');
        $this->setChecksum('gtin');
    }

    /**
     * Allows X on length of 8 chars
     *
     * @param  string $value The barcode to check for allowed characters
     * @return boolean
     */
    public function hasValidCharacters($value)
    {
        if (strlen($value) != 8) {
            if (strpos($value, 'X') !== false) {
                return false;
            }
        }

        return parent::hasValidCharacters($value);
    }

    /**
     * Validates the checksum
     *
     * @param  string $value The barcode to check the checksum for
     * @return boolean
     */
    public function hasValidChecksum($value)
    {
        if (strlen($value) == 8) {
            $this->setChecksum('issn');
        } else {
            $this->setChecksum('gtin');
        }

        return parent::hasValidChecksum($value);
    }

    /**
     * Validates the checksum ()
     * ISSN implementation (reversed mod11)
     *
     * @param  string $value The barcode to validate
     * @return boolean
     */
    protected function issn($value)
    {
        $checksum = substr($value, -1, 1);
        $values   = str_split(substr($value, 0, -1));
        $check    = 0;
        $multi    = 8;
        foreach ($values as $token) {
            if ($token == 'X') {
                $token = 10;
            }

            $check += ($token * $multi);
            --$multi;
        }

        $check %= 11;
        $check  = ($check === 0 ? 0 : (11 - $check));
        if ($check == $checksum) {
            return true;
        } elseif (($check == 10) && ($checksum == 'X')) {
            return true;
        }

        return false;
    }
}
