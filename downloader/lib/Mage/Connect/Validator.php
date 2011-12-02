<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to validate string resources
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Connect_Validator
{
    protected static $_stability = array(0=>'devel',1=>'alpha',2=>'beta',3=>'stable');


    public static function getStabilities()
    {
        return self::$_stability;
    }



    /**
     * Compare stabilities. Returns:
     *
     * -1 if the first stability is lower than the second
     *  0 if they are equal
     *  1 if the second is lower.
     * @param $s1
     * @param $s2
     * @return int
     */
    public function compareStabilities($s1, $s2)
    {
        $list = $this->getStabilities();
        $tmp = array_combine(array_values($list),array_keys($list));

        if(!isset($tmp[$s1], $tmp[$s2])) {
            throw new Exception("Invalid stability in compareStabilities argument");
        }


        // 'stable' turns to 3
        // 'devel' turns to 0
        $s1 = $tmp[$s1];
        $s2 = $tmp[$s2];
        if($s1 === $s2) {
            return 0;
        } elseif($s1 > $s2) {
            return 1;
        } elseif($s1 < $s2) {
            return -1;
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Validate max len of string
     * @param string $str
     * @param int $maxLen
     * @return bool
     */
    public function validateMaxLen($str, $maxLen)
    {
        return strlen((string) $str) <= (int) $maxLen;
    }

    /**
    * Validate channel name and url
    *
    * @param mixed $str
    * @return bool
    */
    public function validateChannelNameOrUri($str)
    {
        return ( $this->validateUrl($str) || $this->validatePackageName($str));
    }

    /**
    * Validate License url
    *
    * @param mixed $str
    * @return boolean
    */
    public function validateLicenseUrl($str)
    {
        if ($str) {
            return ( $this->validateUrl($str) || $this->validatePackageName($str));
        }
        return true;
    }

    /**
     * Validate compatible data
     * @param array $data
     * @return bool
     */
    public function validateCompatible(array $data)
    {
        if(!count($data)) {
            /**
             * Allow empty
             */
            return true;
        }
        $count = 0;
        foreach($data as $k=>$v) {
           foreach(array('name','channel','min','max') as $fld) {
                 $$fld = trim($v[$fld]);
           }
           $count++;

           $res = $this->validateUrl($channel) && strlen($channel);
           if(!$res) {
              $this->addError("Invalid or empty channel in compat. #{$count}");
           }

           $res = $this->validatePackageName($name) && strlen($name);
           if(!$res) {
              $this->addError("Invalid or empty name in compat. #{$count}");
           }
           $res1 = $this->validateVersion($min);
           if(!$res1) {
              $this->addError("Invalid or empty minVersion in compat. #{$count}");
           }
           $res2 = $this->validateVersion($max);
           if(!$res2) {
              $this->addError("Invalid or empty maxVersion in compat. #{$count}");
           }
           if($res1 && $res2 && $this->versionLower($max, $min)) {
                 $this->addError("Max version is lower than min in compat #{$count}");
           }

        }
        return ! $this->hasErrors();
    }

    /**
     * Validate authors of package
     * @param array $authors
     * @return bool
     */
    public function validateAuthors(array $authors)
    {
        if(!count($authors)) {
            $this->addError('Empty authors section');
            return false;
        }
        $count = 0;
        foreach($authors as $k=>$v) {
           $count++;
           array_map('trim', $v);
           $name = $v['name'];
           $login = $v['user'];
           $email = $v['email'];
           $res = $this->validateMaxLen($name, 256) && strlen($name);
           if(!$res) {
              $this->addError("Invalid or empty name for author #{$count}");
           }
           $res = $this->validatePackageName($login) && strlen($login);
           if(!$res) {
                 $this->addError("Invalid or empty login for author #{$count}");
           }
           $res = $this->validateEmail($email);
           if(!$res) {
                 $this->addError("Invalid or empty email for author #{$count}");
           }
        }
        return ! $this->hasErrors();
    }


    /**
     * Validator errors
     * @var array
     */
    private $_errors = array();

    /**
     * Add error
     * @param string $err
     * @return
     */
    private function addError($err)
    {
        $this->_errors[] = $err;
    }

    /**
     * Set validator errors
     * @param array $err
     * @return
     */

    private function setErrors(array $err)
    {
        $this->_errors = $err;
    }

    /**
     * Clear validator errors
     * @return
     */
    private function clearErrors()
    {
        $this->_errors = array();
    }

    /**
     * Check if there are validator errors set
     * @return unknown_type
     */
    public function hasErrors()
    {
        return count($this->_errors) != 0;
    }


    /**
     * Get errors
     * @param bool $clear if true after this call erros will be cleared
     * @return array
     */
    public function getErrors($clear = true)
    {
        $out = $this->_errors;
        if($clear) {
            $this->clearErrors();
        }
        return $out;
    }

    /**
     * Validate URL
     * @param string $str
     * @return bool
     */
    public function validateUrl($str)
    {
        $regex = "@([0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}|"
        ."(((news|telnet|nttp|file|http|ftp|https)://)|(www|ftp)"
        ."[-A-Za-z0-9]*\\.)[-A-Za-z0-9\\.]+)(:[0-9]*)?@i";
        return preg_match($regex, $str);
    }


    /**
     * Validates package stability
     * @param string $str
     * @return bool
     */
    public function validateStability($str)
    {
        return in_array(strval($str), self::$_stability);
    }

    /**
     * Validate date format
     * @param $date
     * @return bool
     */
    public function validateDate($date)
    {
        $subs = null;
        $check1 = preg_match("/^([\d]{4})-([\d]{2})-([\d]{2})$/i", $date, $subs);
        if(!$check1) {
            return false;
        }
        return checkdate($subs[2], $subs[3], $subs[1]);
    }


    /**
     * Validate email
     * @param string $email
     * @return bool
     */
    public function validateEmail($email)
    {
        return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email);
    }

    /**
     * Validate package name
     * @param $name
     * @return bool
     */
    public function validatePackageName($name)
    {
        return preg_match("/^[a-zA-Z0-9_]+$/i", $name);
    }

    /**
     * Validate version number
     * @param string $version
     * @return bool
     */
    public function validateVersion($version)
    {
        return preg_match("/^[\d]+\.[\d]+\.[\d]+([[:alnum:]\.\-\_]+)?$/i", $version);
    }

    /**
     * Check versions are equal
     * @param string $v1
     * @param string $v2
     * @return bool
     */
    public function versionEqual($v1, $v2)
    {
        return version_compare($v1, $v2, "==");
    }

    /**
     * Check version $v1 <= $v2
     * @param string $v1
     * @param string $v2
     * @return bool
     */
    public function versionLowerEqual($v1, $v2)
    {
        return version_compare($v1, $v2, "le");
    }


    /**
     * Check if version $v1 lower than $v2
     * @param string $v1
     * @param string $v2
     * @return bool
     */
    public function versionLower($v1, $v2)
    {
        return version_compare($v1, $v2, "<");
    }

    /**
     * Check version $v1 >= $v2
     * @param string $v1
     * @param string $v2
     * @return bool
     */
    public function versionGreaterEqual($v1, $v2)
    {
        return version_compare($v1, $v2, "ge");
    }


    /**
     * Generic regex validation
     * @param string $str
     * @param string $regex
     * @return bool
     */
    public function validateRegex($str, $regex)
    {
        return preg_match($regex, $str);
    }


    /**
     * Check if PHP extension loaded
     * @param string $name Extension name
     * @return bool
     */
    public function validatePhpExtension($name)
    {
        return extension_loaded($name);
    }


    public function validatePHPVersion($min, $max, $ver = PHP_VERSION)
    {
        $minAccepted = true;
        if($min) {
            $minAccepted = version_compare($ver, $min, ">=");
        }
        $maxAccepted = true;
        if($max) {
            $maxAccepted = version_compare($ver, $max, "<=");
        }
        return (bool) $minAccepted && $maxAccepted;
    }

    /**
     * Validate contents of package
     *
     * @param array $contents
     * @param Mage_Connect_Config $config
     * @return bool
     */
    public function validateContents(array $contents, $config)
    {
        if (!count($contents)) {
            $this->addError('Empty package contents section');
            return false;
        }

        $targetPath = rtrim($config->magento_root, "\\/");
        foreach ($contents as $file) {
            $dest = $targetPath . DS . $file;
            if (file_exists($dest)) {
                $this->addError("'{$file}' already exists");
                return false;
            }
        }
        return true;
    }
}
