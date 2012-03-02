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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect Theme model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Theme
{
    /**
     * Current theme file
     *
     * @var string|null
     */
    protected $_file = null;

    /**
     * Loaded theme xml object
     *
     * @var SimpleXMLElement|null
     */
    protected $_xml = null;

    /**
     * Theme configuration
     *
     * @var array|null
     */
    protected $_conf = null;

    /**
     * Load Theme xml from $file
     *
     * @param string $file
     * @throws Mage_Core_Exception
     */
    public function __construct($file)
    {
        $this->_file = $file;
        if (!file_exists($file)) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('File doesn\'t exist "%s".', $file));
        }
        if (!is_readable($file)) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t read file "%s".', $file));
        }
        try {
            $text = file_get_contents($file);
            $this->_xml = simplexml_load_string($text);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t load XML.'));
        }
        if (empty($this->_xml)) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Invalid XML.'));
        }
        $this->_conf = $this->_xmlToArray($this->_xml->configuration);
        $this->_conf = $this->_conf['configuration'];
        if (!is_array($this->_conf)) {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Wrong theme format.'));
        }
    }

    /**
     * Get theme xml as array
     *
     * @param array $xml
     * @return array
     */
    protected function _xmlToArray($xml)
    {
        $result = array();
        foreach ($xml as $key => $value) {
            if (count($value)) {
                $result[$key] = $this->_xmlToArray($value);
            } else {
                $result[$key] = (string) $value;
            }
        }
        return $result;
    }

    /**
     * Get theme file
     *
     * @return null|string
     */
    protected function _getThemeFile()
    {
        return $this->_file;
    }

    /**
     * Get theme name
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->_xml->manifest->name;
    }

    /**
     * Set theme name
     *
     * @param  $name
     * @return Mage_XmlConnect_Model_Theme
     */
    public function setName($name)
    {
        $name = trim((string) $name);
        $this->_xml->manifest->name = htmlentities($name, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    /**
     * Get theme Label
     *
     * @return string
     */
    public function getLabel()
    {
        return (string) $this->_xml->manifest->label;
    }

    /**
     * Set theme Label
     *
     * @param  $label
     * @return Mage_XmlConnect_Model_Theme
     */
    public function setLabel($label)
    {
        $label = trim((string) $label);
        $this->_xml->manifest->label = htmlentities($label, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    /**
     * Generate full file name for custome theme in media
     *
     * @return string
     */
    protected function _createThemeName()
    {
        /** @var $themesHelper Mage_XmlConnect_Helper_Theme */
        $themesHelper = Mage::helper('Mage_XmlConnect_Helper_Theme');
        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('Mage_Core_Helper_Data');

        $themeFileName = $themesHelper->getMediaThemePath() . DS .$themesHelper->getCustomThemeName() . '_' . time()
            . '_' . $coreHelper->getRandomString(10, 'abcdefghijklmnopqrstuvwxyz0123456789') . '.xml';
        return $themeFileName;
    }

    /**
     * Copy current theme to specified file
     *
     * @param  $filePath
     * @return string new file
     */
    protected function _createCopy($filePath)
    {
        $currentThemeFileName = $this->_getThemeFile();

        $ioFile = new Varien_Io_File();
        if (!$ioFile->cp($currentThemeFileName, $filePath)) {
            Mage::throwException(
                Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t copy file "%s" to "%s".', $currentThemeFileName, $filePath)
            );
        } else {
            $ioFile->chmod($filePath, 0755);
        }

        return $filePath;
    }

    /**
     * Create a copy of current instance with specified data
     *
     * @param  $themeName new theme label
     * @param  $data theme config array
     * @return Mage_XmlConnect_Model_Theme
     */
    public function createNewTheme($themeName, $data)
    {
        $filePath = $this->_createThemeName();
        $themeFileName = $this->_createCopy($filePath);

        /** @var $themeFileName Mage_XmlConnect_Model_Theme */
        $themeFileName = Mage::getModel('Mage_XmlConnect_Model_Theme', $filePath);
        $themeFileName->setLabel($themeName);
        $fileName = basename($filePath);
        $themeFileName->setName(substr($fileName, 0, -4));
        $themeFileName->importAndSaveData($data);
        return $themeFileName;
    }

    /**
     * Load data (flat array) for Varien_Data_Form
     *
     * @return array
     */
    public function getFormData()
    {
        return $this->_flatArray($this->_conf, 'conf');
    }

    /**
     * Load data (flat array) for Varien_Data_Form
     *
     * @param array $subtree
     * @param string $prefix
     * @return array
     */
    protected function _flatArray($subtree, $prefix = null)
    {
        $result = array();
        foreach ($subtree as $key => $value) {
            if (is_null($prefix)) {
                $name = $key;
            } else {
                $name = $prefix . '[' . $key . ']';
            }

            if (is_array($value)) {
                $result = array_merge($result, $this->_flatArray($value, $name));
            } else {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * Validate input Array, recursive
     *
     * @param array $data
     * @param array $xml
     * @return array
     */
    protected function _validateFormInput($data, $xml = null)
    {
        $root = false;
        $result = array();
        if (is_null($xml)) {
            $root = true;
            $data = array('configuration' => $data);
            $xml = $this->_xml->configuration;
        }
        foreach ($xml as $key => $value) {
            if (isset($data[$key])) {
                if (is_array($data[$key])) {
                    $result[$key] = $this->_validateFormInput($data[$key], $value);
                } else {
                    $result[$key] = $data[$key];
                }
            }
        }
        if ($root) {
            $result = $result['configuration'];
        }
        return $result;
    }

    /**
     * Build XML object recursively from $data array
     *
     * @param SimpleXMLElement $parent
     * @param array $data
     * @return null
     */
    protected function _buildRecursive($parent, $data)
    {
        foreach ($data as $key=>$value) {
            if (is_array($value)) {
                $this->_buildRecursive($parent->addChild($key), $value);
            } else {
                $parent->addChild($key, $value);
            }
        }
    }

    /**
     * Import data into theme form $data array, and save XML to file
     *
     * @param array $data
     * @return null
     */
    public function importAndSaveData($data)
    {
        $xml = new SimpleXMLElement('<theme>'.$this->_xml->manifest->asXML().'</theme>');
        $this->_buildRecursive($xml->addChild('configuration'), $this->_validateFormInput($data));
        clearstatcache();
        if (is_writeable($this->_file)) {
            file_put_contents($this->_file, $xml->asXML());
        } else {
            Mage::throwException(Mage::helper('Mage_XmlConnect_Helper_Data')->__('Can\'t write to file "%s".', $this->_file));
        }
    }
}
