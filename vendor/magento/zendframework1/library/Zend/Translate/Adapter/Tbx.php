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

/** @see Zend_Xml_Exception */
#require_once 'Zend/Xml/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate_Adapter_Tbx extends Zend_Translate_Adapter {
    // Internal variables
    private $_file        = false;
    private $_cleared     = array();
    private $_langset     = null;
    private $_termentry   = null;
    private $_content     = null;
    private $_term        = null;
    private $_data        = array();

    /**
     * Load translation data (TBX file reader)
     *
     * @param  string  $filename  TBX file to add, full path must be given for access
     * @param  string  $locale    Locale has no effect for TBX because TBX defines all languages within
     *                            the source file
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

        $encoding = $this->_findEncoding($filename);
        $this->_file = xml_parser_create($encoding);
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
        if ($this->_term !== null) {
            $this->_content .= "<".$name;
            foreach($attrib as $key => $value) {
                $this->_content .= " $key=\"$value\"";
            }
            $this->_content .= ">";
        } else {
            switch(strtolower($name)) {
                case 'termentry':
                    $this->_termentry = null;
                    break;
                case 'langset':
                    if (isset($attrib['xml:lang']) === true) {
                        $this->_langset = $attrib['xml:lang'];
                        if (isset($this->_data[$this->_langset]) === false) {
                            $this->_data[$this->_langset] = array();
                        }
                    }
                    break;
                case 'term':
                    $this->_term    = true;
                    $this->_content = null;
                    break;
                default:
                    break;
            }
        }
    }

    private function _endElement($file, $name)
    {
        if (($this->_term !== null) and ($name != "term")) {
            $this->_content .= "</".$name.">";
        } else {
            switch (strtolower($name)) {
                case 'langset':
                    $this->_langset = null;
                    break;
                case 'term':
                    $this->_term = null;
                    if (empty($this->_termentry)) {
                        $this->_termentry = $this->_content;
                    }
                    if (!empty($this->_content) or (isset($this->_data[$this->_langset][$this->_termentry]) === false)) {
                        $this->_data[$this->_langset][$this->_termentry] = $this->_content;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    private function _contentElement($file, $data)
    {
        if ($this->_term !== null) {
            $this->_content .= $data;
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
        return "Tbx";
    }
}
