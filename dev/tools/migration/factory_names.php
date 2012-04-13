<?php
/**
 * Automated replacement of factory names into real ones
 *
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
 * @category    Magento
 * @package     tools
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require realpath(dirname(dirname(dirname(__DIR__)))) . '/dev/tests/static/framework/bootstrap.php';

// PHP code
foreach (Utility_Files::init()->getPhpFiles(true, true, true, false) as $file) {
    $content = file_get_contents($file);
    $classes = Legacy_ClassesTest::collectPhpCodeClasses($content);
    $factoryNames = array_filter($classes, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = array();
    $replace = array();
    foreach ($factoryNames as $factoryName) {
        list($module, $name) = getModuleName($factoryName);
        addReplace($factoryName, $module, $name, '::getModel(\'%s\'', '_Model_', $search, $replace);
        addReplace($factoryName, $module, $name, '::getSingleton(\'%s\'', '_Model_', $search, $replace);
        addReplace($factoryName, $module, $name, '::getResourceModel(\'%s\'', '_Model_Resource_', $search, $replace);
        addReplace($factoryName, $module, $name, "::getResourceSingleton('%s'", '_Model_Resource_', $search, $replace);
        addReplace($factoryName, $module, $name, 'addBlock(\'%s\'', '_Block_', $search, $replace);
        addReplace($factoryName, $module, $name, 'createBlock(\'%s\'', '_Block_', $search, $replace);
        addReplace($factoryName, $module, $name, 'getBlockClassName(\'%s\'', '_Block_', $search, $replace);
        addReplace($factoryName, $module, $name, 'getBlockSingleton(\'%s\'', '_Block_', $search, $replace);
        addReplace($factoryName, $module, $name, 'helper(\'%s\'', '_Helper_', $search, $replace);
    }
    replaceAndOutput($file, $search, $replace, $factoryNames);
}

// layouts
$layouts = Utility_Files::init()->getLayoutFiles(array(), false);
foreach ($layouts as $file) {
    $xml = simplexml_load_file($file);
    $classes = Utility_Classes::collectLayoutClasses($xml);
    $factoryNames = array_filter($classes, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = array();
    $replace = array();
    foreach ($factoryNames as $factoryName) {
        list($module, $name) = getModuleName($factoryName);
        addReplace($factoryName, $module, $name, 'type="%s"', '_Block_', $search, $replace);
    }
    replaceAndOutput($file, $search, $replace, $factoryNames);
}

// modules in configuration and layouts
$configs = Utility_Files::init()->getConfigFiles('*.xml', array('wsdl.xml', 'wsdl2.xml', 'wsi.xml'), false);
foreach (array_merge($layouts, $configs) as $file) {
    $modules = array_unique(Utility_Classes::getXmlAttributeValues(simplexml_load_file($file), '//@module', 'module'));
    $factoryNames = array_filter($modules, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = array();
    $replace = array();
    foreach ($factoryNames as $factoryName) {
        list($module,) = getModuleName($factoryName);
        $search[] = 'module="' . $factoryName . '"';
        $replace[] = 'module="' . implode('_', array_map('ucfirst', explode('_', $module))) . '"';
    }
    replaceAndOutput($file, $search, $replace, $factoryNames);
}

/**
 * Whether the given class name is a factory name
 *
 * @param string $class
 * @return bool
 */
function isFactoryName($class)
{
    return false !== strpos($class, '/') || preg_match('/^[a-z\d]+(_[A-Za-z\d]+)?$/', $class);
}

/**
 * Transform factory name into a pair of module and name
 *
 * @param string $factoryName
 * @return array
 */
function getModuleName($factoryName)
{
    if (false !== strpos($factoryName, '/')) {
        list($module, $name) = explode('/', $factoryName);
    } else {
        $module = $factoryName;
        $name = false;
    }
    if (false === strpos($module, '_')) {
        $module = "Mage_{$module}";
    }
    return array($module, $name);
}

/**
 * Add search/replacements of factory name into real name based on a specified "sprintf()" pattern
 *
 * @param string $factoryName
 * @param string $module
 * @param string $name
 * @param string $pattern
 * @param string $suffix
 * @param array &$search
 * @param array &$replace
 */
function addReplace($factoryName, $module, $name, $pattern, $suffix, &$search, &$replace)
{
    if (empty($name)) {
        if ('_Helper_' !== $suffix) {
            return;
        }
        $name = 'data';
    }
    $realName = implode('_', array_map('ucfirst', explode('_', $module . $suffix . $name)));
    $search[] = sprintf($pattern, "{$factoryName}");
    $replace[] = sprintf($pattern, "{$realName}");
}

/**
 * Perform replacement if needed
 *
 * @param string $file
 * @param array $search
 * @param array $replace
 * @param mixed $output
 */
function replaceAndOutput($file, $search, $replace, $output)
{
    $content = file_get_contents($file);
    $newContent = str_replace($search, $replace, $content);
    if ($newContent != $content) {
        echo "{$file}\n";
        print_r($output);
        file_put_contents($file, $newContent);
    }
}
