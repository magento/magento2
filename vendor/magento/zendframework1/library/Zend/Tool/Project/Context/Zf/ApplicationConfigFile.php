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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Zf_ApplicationConfigFile extends Zend_Tool_Project_Context_Filesystem_File
{

    /**
     * @var string
     */
    protected $_filesystemName = 'application.ini';

    /**
     * @var string
     */
    protected $_content = null;

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ApplicationConfigFile';
    }

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_ApplicationConfigFile
     */
    public function init()
    {
        $this->_type = $this->_resource->getAttribute('type');
        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array('type' => $this->_type);
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {
        if ($this->_content === null) {
            if (file_exists($this->getPath())) {
                $this->_content = file_get_contents($this->getPath());
            } else {
                $this->_content = $this->_getDefaultContents();
            }

        }

        return $this->_content;
    }

    public function getAsZendConfig($section = 'production')
    {
        return new Zend_Config_Ini($this->getPath(), $section);
    }

    /**
     * addStringItem()
     *
     * @param string $key
     * @param string $value
     * @param string $section
     * @param bool   $quoteValue
     * @return Zend_Tool_Project_Context_Zf_ApplicationConfigFile
     */
    public function addStringItem($key, $value, $section = 'production', $quoteValue = true)
    {
        // null quote value means to auto-detect
        if ($quoteValue === null) {
            $quoteValue = preg_match('#[\"\']#', $value) ? false : true;
        }

        if ($quoteValue == true) {
            $value = '"' . $value . '"';
        }

        $contentLines = preg_split('#[\n\r]#', $this->getContents());

        $newLines = array();
        $insideSection = false;

        foreach ($contentLines as $contentLineIndex => $contentLine) {

            if ($insideSection === false && preg_match('#^\[' . $section . '#', $contentLine)) {
                $insideSection = true;
            }

            $newLines[] = $contentLine;
            if ($insideSection) {
                // if its blank, or a section heading
                if (isset($contentLines[$contentLineIndex + 1]{0}) && $contentLines[$contentLineIndex + 1]{0} == '[') {
                    $newLines[] = $key . ' = ' . $value;
                    $insideSection = null;
                } else if (!isset($contentLines[$contentLineIndex + 1])){
                    $newLines[] = $key . ' = ' . $value;
                    $insideSection = null;
                }
            }
        }

        $this->_content = implode("\n", $newLines);
        return $this;
    }

    /**
     *
     * @param array $item
     * @param string $section
     * @param bool $quoteValue
     * @return Zend_Tool_Project_Context_Zf_ApplicationConfigFile
     */
    public function addItem($item, $section = 'production', $quoteValue = true)
    {
        $stringItems = array();
        $stringValues = array();
        $configKeyNames = array();

        $rii = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($item),
            RecursiveIteratorIterator::SELF_FIRST
            );

        $lastDepth = 0;

        // loop through array structure recursively to create proper keys
        foreach ($rii as $name => $value) {
            $lastDepth = $rii->getDepth();

            if (is_array($value)) {
                array_push($configKeyNames, $name);
            } else {
                $stringItems[] = implode('.', $configKeyNames) . '.' . $name;
                $stringValues[] = $value;
            }
        }

        foreach ($stringItems as $stringItemIndex => $stringItem) {
            $this->addStringItem($stringItem, $stringValues[$stringItemIndex], $section, $quoteValue);
        }

        return $this;
    }

    public function removeStringItem($key, $section = 'production')
    {
        $contentLines = file($this->getPath());

        $newLines = array();
        $insideSection = false;

        foreach ($contentLines as $contentLineIndex => $contentLine) {

            if ($insideSection === false && preg_match('#^\[' . $section . '#', $contentLine)) {
                $insideSection = true;
            }

            if ($insideSection) {
                // if its blank, or a section heading
                if ((trim($contentLine) == null) || ($contentLines[$contentLineIndex + 1][0] == '[')) {
                    $insideSection = null;
                }
            }

            if (!preg_match('#' . $key . '\s?=.*#', $contentLine)) {
                $newLines[] = $contentLine;
            }
        }

        $this->_content = implode('', $newLines);
    }

    public function removeItem($item, $section = 'production')
    {
        $stringItems = array();
        $stringValues = array();
        $configKeyNames = array();

        $rii = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($item),
            RecursiveIteratorIterator::SELF_FIRST
            );

        $lastDepth = 0;

        // loop through array structure recursively to create proper keys
        foreach ($rii as $name => $value) {
            $lastDepth = $rii->getDepth();

            if (is_array($value)) {
                array_push($configKeyNames, $name);
            } else {
                $stringItems[] = implode('.', $configKeyNames) . '.' . $name;
                $stringValues[] = $value;
            }
        }

        foreach ($stringItems as $stringItemIndex => $stringItem) {
            $this->removeStringItem($stringItem, $section);
        }

        return $this;
    }

    protected function _getDefaultContents()
    {

        $contents =<<<EOS
[production]
phpSettings.display_startup_errors = 0
phpSettings.display_errors = 0
includePaths.library = APPLICATION_PATH "/../library"
bootstrap.path = APPLICATION_PATH "/Bootstrap.php"
bootstrap.class = "Bootstrap"
appnamespace = "Application"
resources.frontController.controllerDirectory = APPLICATION_PATH "/controllers"
resources.frontController.params.displayExceptions = 0

[staging : production]

[testing : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1

[development : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1
resources.frontController.params.displayExceptions = 1

EOS;

        return $contents;
    }

}
