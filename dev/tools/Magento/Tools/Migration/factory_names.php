<?php
/**
 * Automated replacement of factory names into real ones
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require realpath(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/dev/tests/static/framework/bootstrap.php';

// PHP code
foreach (\Magento\Framework\Test\Utility\Files::init()->getPhpFiles(true, true, true, false) as $file) {
    $content = file_get_contents($file);
    $classes = \Magento\Framework\Test\Utility\Classes::collectPhpCodeClasses($content);
    $factoryNames = array_filter($classes, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = [];
    $replace = [];
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
$layouts = \Magento\Framework\Test\Utility\Files::init()->getLayoutFiles([], false);
foreach ($layouts as $file) {
    $xml = simplexml_load_file($file);
    $classes = \Magento\Framework\Test\Utility\Classes::collectLayoutClasses($xml);
    $factoryNames = array_filter($classes, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = [];
    $replace = [];
    foreach ($factoryNames as $factoryName) {
        list($module, $name) = getModuleName($factoryName);
        addReplace($factoryName, $module, $name, 'type="%s"', '_Block_', $search, $replace);
    }
    replaceAndOutput($file, $search, $replace, $factoryNames);
}

// modules in configuration and layouts
$configs = \Magento\Framework\Test\Utility\Files::init()->getConfigFiles(
    '*.xml',
    ['wsdl.xml', 'wsdl2.xml', 'wsi.xml'],
    false
);
foreach (array_merge($layouts, $configs) as $file) {
    $modules = array_unique(
        \Magento\Framework\Test\Utility\Classes::getXmlAttributeValues(
            simplexml_load_file($file),
            '//@module',
            'module'
        )
    );
    $factoryNames = array_filter($modules, 'isFactoryName');
    if (!$factoryNames) {
        continue;
    }
    $search = [];
    $replace = [];
    foreach ($factoryNames as $factoryName) {
        list($module, $name) = getModuleName($factoryName);
        if ($module) {
            $search[] = 'module="' . $factoryName . '"';
            $replace[] = 'module="' . implode('\\', array_map('ucfirst', explode('_', $module))) . '"';
        } else {
            $search[] = 'module="' . $factoryName . '"';
            $replace[] = 'module="' . implode('\\', array_map('ucfirst', explode('_', $name))) . '"';
        }
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
    return false !== strpos(
        $class,
        '/'
    ) || preg_match(
        '/^([A-Za-z\\d])+((_[A-Za-z\\d]+))+?$/',
        $class
    ) || preg_match(
        '/^[a-z\d]+(_[A-Za-z\d]+)?$/',
        $class
    );
}

/**
 * Transform factory name into a pair of module and name
 *
 * @param string $factoryName
 * @return array
 */
function getModuleName($factoryName)
{
    if (false !== strpos($factoryName, 'Magento_')) {
        $module = false;
        $name = $factoryName;
    } else {
        if (false !== strpos($factoryName, '/')) {
            list($module, $name) = explode('/', $factoryName);
        } else {
            $module = $factoryName;
            $name = false;
        }

        if (false === strpos($module, '_')) {
            $module = "Magento_{$factoryName}";
        }
    }

    return [$module, $name];
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
 * @return void
 */
function addReplace($factoryName, $module, $name, $pattern, $suffix, &$search, &$replace)
{
    if (empty($name)) {
        if ('_Helper_' !== $suffix) {
            return;
        }
        $name = 'data';
    }

    if (empty($module)) {
        $realName = '\\' . implode('\\', array_map('ucfirst', explode('_', $name)));
    } else {
        $realName = '\\' . implode('\\', array_map('ucfirst', explode('_', $module . $suffix . $name)));
    }

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
 * @return void
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
