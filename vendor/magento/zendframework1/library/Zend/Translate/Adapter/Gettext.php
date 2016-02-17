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
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Locale */
#require_once 'Zend/Locale.php';

/** Zend_Translate_Adapter */
#require_once 'Zend/Translate/Adapter.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate_Adapter_Gettext extends Zend_Translate_Adapter {
    // Internal variables
    private $_bigEndian   = false;
    private $_file        = false;
    private $_adapterInfo = array();
    private $_data        = array();

    /**
     * Read values from the MO file
     *
     * @param  string  $bytes
     */
    private function _readMOData($bytes)
    {
        if ($this->_bigEndian === false) {
            return unpack('V' . $bytes, fread($this->_file, 4 * $bytes));
        } else {
            return unpack('N' . $bytes, fread($this->_file, 4 * $bytes));
        }
    }

    /**
     * Load translation data (MO file reader)
     *
     * @param  string  $filename  MO file to add, full path must be given for access
     * @param  string  $locale    New Locale/Language to set, identical with locale identifier,
     *                            see Zend_Locale for more information
     * @param  array   $option    OPTIONAL Options to use
     * @throws Zend_Translation_Exception
     * @return array
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $this->_data      = array();
        $this->_bigEndian = false;
        $this->_file      = @fopen($filename, 'rb');
        if (!$this->_file) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
        }
        if (@filesize($filename) < 10) {
            @fclose($this->_file);
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('\'' . $filename . '\' is not a gettext file');
        }

        // get Endian
        $input = $this->_readMOData(1);
        if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
            $this->_bigEndian = false;
        } else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
            $this->_bigEndian = true;
        } else {
            @fclose($this->_file);
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('\'' . $filename . '\' is not a gettext file');
        }
        // read revision - not supported for now
        $input = $this->_readMOData(1);

        // number of bytes
        $input = $this->_readMOData(1);
        $total = $input[1];

        // number of original strings
        $input = $this->_readMOData(1);
        $OOffset = $input[1];

        // number of translation strings
        $input = $this->_readMOData(1);
        $TOffset = $input[1];

        // fill the original table
        fseek($this->_file, $OOffset);
        $origtemp = $this->_readMOData(2 * $total);
        fseek($this->_file, $TOffset);
        $transtemp = $this->_readMOData(2 * $total);

        for($count = 0; $count < $total; ++$count) {
            if ($origtemp[$count * 2 + 1] != 0) {
                fseek($this->_file, $origtemp[$count * 2 + 2]);
                $original = @fread($this->_file, $origtemp[$count * 2 + 1]);
                $original = explode("\0", $original);
            } else {
                $original[0] = '';
            }

            if ($transtemp[$count * 2 + 1] != 0) {
                fseek($this->_file, $transtemp[$count * 2 + 2]);
                $translate = fread($this->_file, $transtemp[$count * 2 + 1]);
                $translate = explode("\0", $translate);
                if ((count($original) > 1)) {
                    $this->_data[$locale][$original[0]] = $translate;
                    array_shift($original);
                    foreach ($original as $orig) {
                        $this->_data[$locale][$orig] = '';
                    }
                } else {
                    $this->_data[$locale][$original[0]] = $translate[0];
                }
            }
        }

        @fclose($this->_file);

        $this->_data[$locale][''] = trim($this->_data[$locale]['']);
        if (empty($this->_data[$locale][''])) {
            $this->_adapterInfo[$filename] = 'No adapter information available';
        } else {
            $this->_adapterInfo[$filename] = $this->_data[$locale][''];
        }

        unset($this->_data[$locale]['']);
        return $this->_data;
    }

    /**
     * Returns the adapter informations
     *
     * @return array Each loaded adapter information as array value
     */
    public function getAdapterInfo()
    {
        return $this->_adapterInfo;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return "Gettext";
    }
}
