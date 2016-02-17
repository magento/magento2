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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
#require_once 'Zend/Validate/Abstract.php';

/**
 * Validates IBAN Numbers (International Bank Account Numbers)
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Iban extends Zend_Validate_Abstract
{
    const NOTSUPPORTED = 'ibanNotSupported';
    const FALSEFORMAT  = 'ibanFalseFormat';
    const CHECKFAILED  = 'ibanCheckFailed';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOTSUPPORTED => "Unknown country within the IBAN '%value%'",
        self::FALSEFORMAT  => "'%value%' has a false IBAN format",
        self::CHECKFAILED  => "'%value%' has failed the IBAN check",
    );

    /**
     * Optional locale
     *
     * @var string|Zend_Locale|null
     */
    protected $_locale;

    /**
     * IBAN regexes by region
     *
     * @var array
     */
    protected $_ibanregex = array(
        'AD' => '/^AD[0-9]{2}[0-9]{8}[A-Z0-9]{12}$/',
        'AE' => '/^AE[0-9]{2}[0-9]{3}[0-9]{16}$/',
        'AL' => '/^AL[0-9]{2}[0-9]{8}[A-Z0-9]{16}$/',
        'AT' => '/^AT[0-9]{2}[0-9]{5}[0-9]{11}$/',
        'AZ' => '/^AZ[0-9]{2}[0-9]{4}[A-Z0-9]{20}$/',
        'BA' => '/^BA[0-9]{2}[0-9]{6}[0-9]{10}$/',
        'BE' => '/^BE[0-9]{2}[0-9]{3}[0-9]{9}$/',
        'BG' => '/^BG[0-9]{2}[A-Z]{4}[0-9]{4}[0-9]{2}[A-Z0-9]{8}$/',
        'BH' => '/^BH[0-9]{2}[A-Z]{4}[A-Z0-9]{14}$/',
        'BR' => '/^BR[0-9]{2}[0-9]{8}[0-9]{5}[0-9]{10}[A-Z]{1}[A-Z0-9]{1}$/',
        'CH' => '/^CH[0-9]{2}[0-9]{5}[A-Z0-9]{12}$/',
        'CR' => '/^CR[0-9]{2}[0-9]{3}[0-9]{14}$/',
        'CS' => '/^CS[0-9]{2}[0-9]{3}[0-9]{15}$/',
        'CY' => '/^CY[0-9]{2}[0-9]{8}[A-Z0-9]{16}$/',
        'CZ' => '/^CZ[0-9]{2}[0-9]{4}[0-9]{16}$/',
        'DE' => '/^DE[0-9]{2}[0-9]{8}[0-9]{10}$/',
        'DK' => '/^DK[0-9]{2}[0-9]{4}[0-9]{10}$/',
        'DO' => '/^DO[0-9]{2}[A-Z0-9]{4}[0-9]{20}$/',
        'EE' => '/^EE[0-9]{2}[0-9]{4}[0-9]{12}$/',
        'ES' => '/^ES[0-9]{2}[0-9]{8}[0-9]{12}$/',
        'FR' => '/^FR[0-9]{2}[0-9]{10}[A-Z0-9]{11}[0-9]{2}$/',
        'FI' => '/^FI[0-9]{2}[0-9]{6}[0-9]{8}$/',
        'FO' => '/^FO[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}$/',
        'GB' => '/^GB[0-9]{2}[A-Z]{4}[0-9]{14}$/',
        'GE' => '/^GE[0-9]{2}[A-Z]{2}[0-9]{16}$/',
        'GI' => '/^GI[0-9]{2}[A-Z]{4}[A-Z0-9]{15}$/',
        'GL' => '/^GL[0-9]{2}[0-9]{4}[0-9]{9}[0-9]{1}$/',
        'GR' => '/^GR[0-9]{2}[0-9]{7}[A-Z0-9]{16}$/',
        'GT' => '/^GT[0-9]{2}[A-Z0-9]{4}[A-Z0-9]{20}$/',
        'HR' => '/^HR[0-9]{2}[0-9]{7}[0-9]{10}$/',
        'HU' => '/^HU[0-9]{2}[0-9]{7}[0-9]{1}[0-9]{15}[0-9]{1}$/',
        'IE' => '/^IE[0-9]{2}[A-Z0-9]{4}[0-9]{6}[0-9]{8}$/',
        'IL' => '/^IL[0-9]{2}[0-9]{3}[0-9]{3}[0-9]{13}$/',
        'IS' => '/^IS[0-9]{2}[0-9]{4}[0-9]{18}$/',
        'IT' => '/^IT[0-9]{2}[A-Z]{1}[0-9]{10}[A-Z0-9]{12}$/',
        'KW' => '/^KW[0-9]{2}[A-Z]{4}[0-9]{3}[0-9]{22}$/',
        'KZ' => '/^KZ[A-Z]{2}[0-9]{2}[0-9]{3}[A-Z0-9]{13}$/',
        'LB' => '/^LB[0-9]{2}[0-9]{4}[A-Z0-9]{20}$/',
        'LI' => '/^LI[0-9]{2}[0-9]{5}[A-Z0-9]{12}$/',
        'LU' => '/^LU[0-9]{2}[0-9]{3}[A-Z0-9]{13}$/',
        'LT' => '/^LT[0-9]{2}[0-9]{5}[0-9]{11}$/',
        'LV' => '/^LV[0-9]{2}[A-Z]{4}[A-Z0-9]{13}$/',
        'MC' => '/^MC[0-9]{2}[0-9]{5}[0-9]{5}[A-Z0-9]{11}[0-9]{2}$/',
        'MD' => '/^MD[0-9]{2}[A-Z0-9]{20}$/',
        'ME' => '/^ME[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}$/',
        'MK' => '/^MK[0-9]{2}[A-Z]{3}[A-Z0-9]{10}[0-9]{2}$/',
        'MR' => '/^MR13[0-9]{5}[0-9]{5}[0-9]{11}[0-9]{2}$/',
        'MU' => '/^MU[0-9]{2}[A-Z]{4}[0-9]{2}[0-9]{2}[0-9]{12}[0-9]{3}[A-Z]{2}$/',
        'MT' => '/^MT[0-9]{2}[A-Z]{4}[0-9]{5}[A-Z0-9]{18}$/',
        'NL' => '/^NL[0-9]{2}[A-Z]{4}[0-9]{10}$/',
        'NO' => '/^NO[0-9]{2}[0-9]{4}[0-9]{7}$/',
        'PK' => '/^PK[0-9]{2}[A-Z]{4}[0-9]{16}$/',
        'PL' => '/^PL[0-9]{2}[0-9]{8}[0-9]{16}$/',
        'PS' => '/^PS[0-9]{2}[A-Z]{4}[0-9]{21}$/',
        'PT' => '/^PT[0-9]{2}[0-9]{8}[0-9]{13}$/',
        'RO' => '/^RO[0-9]{2}[A-Z]{4}[A-Z0-9]{16}$/',
        'RS' => '/^RS[0-9]{2}[0-9]{3}[0-9]{13}[0-9]{2}$/',
        'SA' => '/^SA[0-9]{2}[0-9]{2}[A-Z0-9]{18}$/',
        'SE' => '/^SE[0-9]{2}[0-9]{3}[0-9]{17}$/',
        'SI' => '/^SI[0-9]{2}[0-9]{5}[0-9]{8}[0-9]{2}$/',
        'SK' => '/^SK[0-9]{2}[0-9]{4}[0-9]{16}$/',
        'SM' => '/^SM[0-9]{2}[A-Z]{1}[0-9]{5}[0-9]{5}[A-Z0-9]{12}$/',
        'TN' => '/^TN[0-9]{2}[0-9]{5}[0-9]{15}$/',
        'TR' => '/^TR[0-9]{2}[0-9]{5}[A-Z0-9]{17}$/',
        'VG' => '/^VG[0-9]{2}[A-Z]{4}[0-9]{16}$/'
    );

    /**
     * Sets validator options
     *
     * @param string|Zend_Config|Zend_Locale $locale OPTIONAL
     */
    public function __construct($locale = null)
    {
        if ($locale instanceof Zend_Config) {
            $locale = $locale->toArray();
        }

        if (is_array($locale)) {
            if (array_key_exists('locale', $locale)) {
                $locale = $locale['locale'];
            } else {
                $locale = null;
            }
        }

        if (empty($locale)) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Locale')) {
                $locale = Zend_Registry::get('Zend_Locale');
            }
        }

        if ($locale !== null) {
            $this->setLocale($locale);
        }
    }

    /**
     * Returns the locale option
     *
     * @return string|Zend_Locale|null
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale option
     *
     * @param  string|Zend_Locale $locale
     * @throws Zend_Locale_Exception
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Date provides a fluent interface
     */
    public function setLocale($locale = null)
    {
        if ($locale !== false) {
            #require_once 'Zend/Locale.php';
            $locale = Zend_Locale::findLocale($locale);
            if (strlen($locale) < 4) {
                #require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Region must be given for IBAN validation');
            }
        }

        $this->_locale = $locale;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if $value is a valid IBAN
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $value = strtoupper($value);
        $this->_setValue($value);

        if (empty($this->_locale)) {
            $region = substr($value, 0, 2);
        } else {
            $region = new Zend_Locale($this->_locale);
            $region = $region->getRegion();
        }

        if (!array_key_exists($region, $this->_ibanregex)) {
            $this->_setValue($region);
            $this->_error(self::NOTSUPPORTED);
            return false;
        }

        if (!preg_match($this->_ibanregex[$region], $value)) {
            $this->_error(self::FALSEFORMAT);
            return false;
        }

        $format = substr($value, 4) . substr($value, 0, 4);
        $format = str_replace(
            array('A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M',
                  'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z'),
            array('10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22',
                  '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35'),
            $format);

        $temp = intval(substr($format, 0, 1));
        $len  = strlen($format);
        for ($x = 1; $x < $len; ++$x) {
            $temp *= 10;
            $temp += intval(substr($format, $x, 1));
            $temp %= 97;
        }

        if ($temp != 1) {
            $this->_error(self::CHECKFAILED);
            return false;
        }

        return true;
    }
}
