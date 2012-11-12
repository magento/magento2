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
 * @category   Tools
 * @package    DI
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Require necessary files
 */
/**
 * Constants definition
 */
use \Zend\Di\Di;

define('DS', DIRECTORY_SEPARATOR);
define('BP', realpath(__DIR__ . '/../../..'));

require_once BP . '/lib/Magento/Autoload.php';
require_once BP . '/app/code/core/Mage/Core/functions.php';
require_once BP . '/app/Mage.php';

$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'local';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'community';
$paths[] = BP . DS . 'app' . DS . 'code' . DS . 'core';
$paths[] = BP . DS . 'lib';
Magento_Autoload::getInstance()->addIncludePath($paths);

Mage::setRoot();
$config = new Mage_Core_Model_Config(new Magento_ObjectManager_Zend());
$config->loadBase();
$config->loadModules();

$definitions = array();

/**
 * Compile definitions using Magento_Di_Definition_CompilerDefinition_Zend
 *
 * @param string $moduleDir
 * @return array
 */
function compileModuleDefinitions($moduleDir)
{
    $strategy = new \Zend\Di\Definition\IntrospectionStrategy(new \Zend\Code\Annotation\AnnotationManager());
    $strategy->setMethodNameInclusionPatterns(array());
    $strategy->setInterfaceInjectionInclusionPatterns(array());

    $compiler = new Magento_Di_Definition_CompilerDefinition_Zend($strategy);
    $compiler->addDirectory($moduleDir);

    $controllerPath = $moduleDir . '/controllers/';
    if (file_exists($controllerPath)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerPath)) as $file) {
            if (!$file->isDir()) {
                require_once $file->getPathname();
            }
        }
    }

    $compiler->compile();
    $moduleDefinitions = $compiler->toArray();
    array_walk($moduleDefinitions, function (&$item)
    {
        unset($item['supertypes']);
    });

    return $moduleDefinitions;
}

/**
 * Remove model and block constructors
 *
 * @see Zend\Di\Di::newInstance()
 * @param $moduleDefinitions
 */
function removeModelAndBlockConstructors(&$moduleDefinitions)
{
    foreach ($moduleDefinitions as $name => $definition) {
        $constructorParams = array();
        if (isset($definition['parameters']['__construct'])) {
            $constructorParams = array_values($definition['parameters']['__construct']);
        }
        //TODO: fix this condition
        if (!count($constructorParams)
            || (count($constructorParams) == 5 && preg_match('/\w*_\w*\_Model/', $name))
            || (count($constructorParams) == 10 && preg_match('/\w*_\w*\_Block/', $name))) {
            unset($moduleDefinitions[$name]);
        }
    }
}

/**
 * Compile module definitions
 *
 * @param string $moduleDir
 * @return array
 */
function compileModule($moduleDir)
{
    $moduleDefinitions = compileModuleDefinitions($moduleDir);
    removeModelAndBlockConstructors($moduleDefinitions);

    return $moduleDefinitions;
}

foreach (glob(BP . '/app/code/*') as $codePoolDir) {
    foreach (glob($codePoolDir . '/*') as $vendorDir) {
        foreach (glob($vendorDir . '/*') as $moduleDir) {
            $moduleName = basename($vendorDir) . '_' . basename($moduleDir);
            if (is_dir($moduleDir) && $config->isModuleEnabled($moduleName)) {
                echo "Compiling module " . $moduleName . "\n";
                $definitions = array_merge_recursive($definitions, compileModule($moduleDir));
            }
        }
    }
}

echo "Compiling Varien\n";
$definitions = array_merge_recursive($definitions, compileModule(BP . '/lib/Varien'));
echo "Compiling Magento\n";
$definitions = array_merge_recursive($definitions, compileModule(BP . '/lib/Magento'));
echo "Compiling Mage\n";
$definitions = array_merge_recursive($definitions, compileModule(BP . '/lib/Mage'));

foreach ($definitions as $key => $definition) {
    $definitions[$key] = json_encode($definition);
}
if (!file_exists(BP . '/var/di/')) {
    mkdir(BP . '/var/di', 0777, true);
}

file_put_contents(BP . '/var/di/definitions.php', serialize($definitions));
//file_put_contents(BP . '/var/di/definitions.php', '<?php return ' . var_export($definitions, true) . ';');
