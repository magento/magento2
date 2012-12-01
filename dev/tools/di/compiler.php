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
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Constants definition
 */
define('DS', DIRECTORY_SEPARATOR);
define('BP', realpath(__DIR__ . '/../../..'));
/**
 * Require necessary files
 */
require_once BP . '/app/code/core/Mage/Core/functions.php';
require_once BP . '/app/Mage.php';

require __DIR__ . '/../../../app/autoload.php';
Magento_Autoload_IncludePath::addIncludePath(array(
    BP . DS . 'app' . DS . 'code' . DS . 'local',
    BP . DS . 'app' . DS . 'code' . DS . 'community',
    BP . DS . 'app' . DS . 'code' . DS . 'core',
    BP . DS . 'lib',
));
Mage::setRoot();
$definitions = array();

class ArrayDefinitionCompiler
{
    /**#@+
     * Abstract classes
     */
    const ABSTRACT_MODEL = 'Mage_Core_Model_Abstract';
    const ABSTRACT_BLOCK = 'Mage_Core_Block_Abstract';
    /**#@-*/

    /**
     * Main config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * Information about abstract block/model constructor
     *
     * @var ReflectionMethod[]
     */
    protected $_constructorList;

    /**
     * List of common dependencies for model and block abstract
     *
     * @var array
     */
    protected $_commonDependencies = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_config = new Mage_Core_Model_Config(new Magento_ObjectManager_Zend());
        $this->_config->loadBase();
        $this->_config->loadModules();

        $this->_initCommonDependencies();
    }

    /**
     * Compile module definitions
     *
     * @param string $moduleDir
     * @return array
     */
    public function compileModule($moduleDir)
    {
        $moduleDefinitions = $this->_compileModuleDefinitions($moduleDir);
        $this->_removeModelAndBlockConstructors($moduleDefinitions);

        array_walk($moduleDefinitions, function (&$item)
        {
            unset($item['supertypes']);
        });

        return $moduleDefinitions;
    }

    /**
     * Check is module enabled
     *
     * @param string $moduleName
     * @return bool
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->_config->isModuleEnabled($moduleName);
    }

    /**
     * Init list of common dependencies of model and block abstract classes
     *
     * @return ArrayDefinitionCompiler
     */
    protected function _initCommonDependencies()
    {
        $classList = array(
            self::ABSTRACT_MODEL,
            self::ABSTRACT_BLOCK
        );

        foreach ($classList as $className) {
            $this->_constructorList[$className] = new ReflectionMethod($className, '__construct');

            /** @var $param ReflectionParameter */
            foreach ($this->_constructorList[$className]->getParameters() as $param) {
                if ($param->getClass() && !in_array($param->getClass()->getName(), $this->_commonDependencies)) {
                    $this->_commonDependencies[] = $param->getClass()->getName();
                }
            }
        }

        return $this;
    }

    /**
     * Compile definitions using Magento_Di_Definition_CompilerDefinition_Zend
     *
     * @param string $moduleDir
     * @return array
     */
    protected function _compileModuleDefinitions($moduleDir)
    {
        $strategy = new \Zend\Di\Definition\IntrospectionStrategy(new \Zend\Code\Annotation\AnnotationManager());
        $strategy->setMethodNameInclusionPatterns(array());
        $strategy->setInterfaceInjectionInclusionPatterns(array());

        $compiler = new Magento_Di_Definition_CompilerDefinition_Zend($strategy);
        $compiler->addDirectory($moduleDir);

        $controllerPath = $moduleDir . '/controllers/';
        if (file_exists($controllerPath)) {
            /** @var $file DirectoryIterator */
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllerPath)) as $file) {
                if (!$file->isDir()) {
                    require_once $file->getPathname();
                }
            }
        }

        $compiler->compile();
        $moduleDefinitions = $compiler->toArray();

        return $moduleDefinitions;
    }

    /**
     * Remove model and block constructors
     *
     * @see Zend\Di\Di::newInstance()
     * @param $moduleDefinitions
     */
    protected function _removeModelAndBlockConstructors(&$moduleDefinitions)
    {
        foreach ($moduleDefinitions as $name => $definition) {
            if (!$this->_hasConstructorParams($name, $definition)
                || $this->_areConstructorParamsEquals(self::ABSTRACT_MODEL, $definition)
                || $this->_areConstructorParamsEquals(self::ABSTRACT_BLOCK, $definition)
            ) {
                unset($moduleDefinitions[$name]);
            }
        }
    }

    /**
     * Check if class has constructor params
     *
     * For cases when *_Model_* found function will return true, because such classes must be in compiled array.
     *
     * @param $className
     * @param $definition
     * @return bool
     */
    protected function _hasConstructorParams($className, $definition)
    {
        $constructorParams = array();
        if (isset($definition['parameters']['__construct'])) {
            $constructorParams = array_values($definition['parameters']['__construct']);
        }

        $paramNumber = count($constructorParams);
        if (!$paramNumber && in_array($className, $this->_commonDependencies)) {
            return true;
        }

        return (bool) $paramNumber;
    }

    /**
     * Check is class constructor params are same as in abstract
     *
     * @param string $className
     * @param array $definition
     * @return bool
     */
    protected function _areConstructorParamsEquals($className, $definition)
    {
        if (!isset($this->_constructorList[$className])) {
            $this->_constructorList[$className] = new ReflectionMethod($className, '__construct');
        }

        if (isset($definition['supertypes']) && isset($definition['parameters']['__construct'])) {
            foreach ($definition['supertypes'] as $type) {
                if (($type == $className)
                    && (count($definition['parameters']['__construct']) ==
                        count($this->_constructorList[$className]->getParameters())
                    )
                ) {
                    return $this->_compareConstructorParams($definition['parameters']['__construct'],
                        $this->_constructorList[$className]->getParameters()
                    );
                }
            }
        }

        return false;
    }

    /**
     * Compare constructors params
     *
     * @param array $classArguments
     * @param ReflectionParameter[] $abstractArguments
     * @return bool
     */
    protected function _compareConstructorParams($classArguments, $abstractArguments)
    {
        $index = 0;
        foreach ($classArguments as $argumentInfo) {
            $argumentType = null;
            if ($abstractArguments[$index]->getClass()) {
                $argumentType = $abstractArguments[$index]->getClass()->getName();
            }
            if ($argumentInfo[1] != $argumentType) {
                return false;
            }
            $index++;
        }
        return true;
    }
}

$compiler = new ArrayDefinitionCompiler();

foreach (glob(BP . '/app/code/*') as $codePoolDir) {
    foreach (glob($codePoolDir . '/*') as $vendorDir) {
        foreach (glob($vendorDir . '/*') as $moduleDir) {
            $moduleName = basename($vendorDir) . '_' . basename($moduleDir);
            if (is_dir($moduleDir) && $compiler->isModuleEnabled($moduleName)) {
                echo "Compiling module " . $moduleName . "\n";
                $definitions = array_merge_recursive($definitions, $compiler->compileModule($moduleDir));
            }
        }
    }
}

echo "Compiling Varien\n";
$definitions = array_merge_recursive($definitions, $compiler->compileModule(BP . '/lib/Varien'));
echo "Compiling Magento\n";
$definitions = array_merge_recursive($definitions, $compiler->compileModule(BP . '/lib/Magento'));
echo "Compiling Mage\n";
$definitions = array_merge_recursive($definitions, $compiler->compileModule(BP . '/lib/Mage'));

foreach ($definitions as $key => $definition) {
    $definitions[$key] = json_encode($definition);
}
if (!file_exists(BP . '/var/di/')) {
    mkdir(BP . '/var/di', 0777, true);
}

file_put_contents(BP . '/var/di/definitions.php', serialize($definitions));
