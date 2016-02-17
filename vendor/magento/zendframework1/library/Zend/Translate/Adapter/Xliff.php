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

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/** @See Zend_Xml_Exception */
#require_once 'Zend/Xml/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate_Adapter_Xliff extends Zend_Translate_Adapter {
    // Internal variables
    private $_file        = false;
    private $_useId       = true;
    private $_cleared     = array();
    private $_transunit   = null;
    private $_source      = null;
    private $_target      = null;
    private $_langId      = null;
    private $_scontent    = null;
    private $_tcontent    = null;
    private $_stag        = false;
    private $_ttag        = false;
    private $_data        = array();

    /**
     * Load translation data (XLIFF file reader)
     *
     * @param  string  $locale    Locale/Language to add data for, identical with locale identifier,
     *                            see Zend_Locale for more information
     * @param  string  $filename  XLIFF file to add, full path must be given for access
     * @param  array   $option    OPTIONAL Options to use
     * @throws Zend_Translation_Exception
     * @return array
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $this->_data = array();
        if (!is_readable($filename)) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Translation file \'' . $filename . '\' is not readable.');
        }

        if (empty($options['useId'])) {
            $this->_useId = false;
        } else {
            $this->_useId = true;
        }

        $encoding      = $this->_findEncoding($filename);
        $this->_target = $locale;
        $this->_file   = xml_parser_create($encoding);
        xml_set_object($this->_file, $this);
        xml_parser_set_option($this->_file, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($this->_file, "_startElement", "_endElement");
        xml_set_character_data_handler($this->_file, "_contentElement");

        try {
            Zend_Xml_Security::scanFile($filename);
        } catch (Zend_Xml_Exception $e) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception(
                $e->getMessage()
            );
        }

        if (!xml_parse($this->_file, file_get_contents($filename))) {
            $ex = sprintf('XML error: %s at line %d of file %s',
                          xml_error_string(xml_get_error_code($this->_file)),
                          xml_get_current_line_number($this->_file),
                          $filename);
            xml_parser_free($this->_file);
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception($ex);
        }

        return $this->_data;
    }

    private function _startElement($file, $name, $attrib)
    {
        if ($this->_stag === true) {
            $this->_scontent .= "<".$name;
            foreach($attrib as $key => $value) {
                $this->_scontent .= " $key=\"$value\"";
            }
            $this->_scontent .= ">";
        } else if ($this->_ttag === true) {
            $this->_tcontent .= "<".$name;
            foreach($attrib as $key => $value) {
                $this->_tcontent .= " $key=\"$value\"";
            }
            $this->_tcontent .= ">";
        } else {
            switch(strtolower($name)) {
                case 'file':
                    $this->_source = $attrib['source-language'];
                    if (isset($attrib['target-language'])) {
                        $this->_target = $attrib['target-language'];
                    }

                    if (!isset($this->_data[$this->_source])) {
                        $this->_data[$this->_source] = array();
                    }

                    if (!isset($this->_data[$this->_target])) {
                        $this->_data[$this->_target] = array();
                    }

                    break;
                case 'trans-unit':
                    $this->_transunit = true;
                    $this->_langId = $attrib['id'];
                    break;
                case 'source':
                    if ($this->_transunit === true) {
                        $this->_scontent = null;
                        $this->_stag = true;
                        $this->_ttag = false;
                    }
                    break;
                case 'target':
                    if ($this->_transunit === true) {
                        $this->_tcontent = null;
                        $this->_ttag = true;
                        $this->_stag = false;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function _endElement($file, $name)
    {
        if (($this->_stag === true) and ($name !== 'source')) {
            $this->_scontent .= "</".$name.">";
        } else if (($this->_ttag === true) and ($name !== 'target')) {
            $this->_tcontent .= "</".$name.">";
        } else {
            switch (strtolower($name)) {
                case 'trans-unit':
                    $this->_transunit = null;
                    $this->_langId    = null;
                    $this->_scontent  = null;
                    $this->_tcontent  = null;
                    break;
                case 'source':
                    if ($this->_useId) {
                        if (!empty($this->_scontent) && !empty($this->_langId) &&
                            !isset($this->_data[$this->_source][$this->_langId])) {
                            $this->_data[$this->_source][$this->_langId] = $this->_scontent;
                        }
                    } else {
                        if (!empty($this->_scontent) &&
                            !isset($this->_data[$this->_source][$this->_scontent])) {
                            $this->_data[$this->_source][$this->_scontent] = $this->_scontent;
                        }
                    }
                    $this->_stag = false;
                    break;
                case 'target':
                    if ($this->_useId) {
                        if (!empty($this->_tcontent) && !empty($this->_langId) &&
                            !isset($this->_data[$this->_target][$this->_langId])) {
                            $this->_data[$this->_target][$this->_langId] = $this->_tcontent;
                        }
                    } else {
                        if (!empty($this->_tcontent) && !empty($this->_scontent) &&
                            !isset($this->_data[$this->_target][$this->_scontent])) {
                            $this->_data[$this->_target][$this->_scontent] = $this->_tcontent;
                        }
                    }
                    $this->_ttag = false;
                    break;
                default:
                    break;
            }
        }
    }

    private function _contentElement($file, $data)
    {
        if (($this->_transunit !== null) and ($this->_source !== null) and ($this->_stag === true)) {
            $this->_scontent .= $data;
        }

        if (($this->_transunit !== null) and ($this->_target !== null) and ($this->_ttag === true)) {
            $this->_tcontent .= $data;
        }
    }

    private function _findEncoding($filename)
    {
        $file = file_get_contents($filename, null, null, 0, 100);
        if (strpos($file, "encoding") !== false) {
            $encoding = substr($file, strpos($file, "encoding") + 9);
            $encoding = substr($encoding, 1, strpos($encoding, $encoding[0], 1) - 1);
            return $encoding;
        }
        return 'UTF-8';
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return "Xliff";
    }
}
