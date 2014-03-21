<?php
/**
 * Compiler test. Check compilation of DI definitions and code generation
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Di;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_command;

    /**
     * @var \Magento\Shell
     */
    protected $_shell;

    /**
     * @var string
     */
    protected $_generationDir;

    /**
     * @var string
     */
    protected $_compilationDir;

    /**
     * @var string
     */
    protected $_tmpDir;

    /**
     * @var \Magento\ObjectManager\Config\Mapper\Dom()
     */
    protected $_mapper;

    /**
     * @var \Magento\Code\Validator
     */
    protected $_validator;

    /**
     * Class arguments reader
     *
     * @var \Magento\Interception\Code\InterfaceValidator
     */
    protected $pluginValidator;

    protected function setUp()
    {
        $this->_shell = new \Magento\Shell(new \Magento\OSInfo());
        $basePath = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        $basePath = str_replace('\\', '/', $basePath);

        $this->_tmpDir = realpath(__DIR__) . '/tmp';
        $this->_generationDir = $this->_tmpDir . '/generation';
        $this->_compilationDir = $this->_tmpDir . '/di';

        \Magento\Autoload\IncludePath::addIncludePath(
            array($basePath . '/app/code', $basePath . '/lib', $this->_generationDir)
        );

        $this->_command = 'php ' . $basePath . '/dev/tools/Magento/Tools/Di/compiler.php --generation=%s --di=%s';
        $this->_mapper = new \Magento\ObjectManager\Config\Mapper\Dom(
            new \Magento\Stdlib\BooleanUtils(),
            new \Magento\ObjectManager\Config\Mapper\ArgumentParser()
        );
        $this->_validator = new \Magento\Code\Validator();
        $this->_validator->add(new \Magento\Code\Validator\ConstructorIntegrity());
        $this->_validator->add(new \Magento\Code\Validator\ContextAggregation());
        $this->_validator->add(new \Magento\Code\Validator\TypeDuplication());
        $this->_validator->add(new \Magento\Code\Validator\ArgumentSequence());
        $this->pluginValidator = new \Magento\Interception\Code\InterfaceValidator();
    }

    protected function tearDown()
    {
        $filesystem = new \Magento\Filesystem\Driver\File();
        if ($filesystem->isExists($this->_tmpDir)) {
            $filesystem->deleteDirectory($this->_tmpDir);
        }
    }

    /**
     * Validate DI config file
     *
     * @param string $file
     */
    protected function _validateFile($file)
    {
        $dom = new \DOMDocument();
        $dom->load($file);
        $data = $this->_mapper->convert($dom);

        foreach ($data as $instanceName => $parameters) {
            if (!isset($parameters['parameters']) || empty($parameters['parameters'])) {
                continue;
            }
            if (\Magento\TestFramework\Utility\Classes::isVirtual($instanceName)) {
                $instanceName = \Magento\TestFramework\Utility\Classes::resolveVirtualType($instanceName);
            }


            if (!$this->_classExistsAsReal($instanceName)) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($instanceName);

            $constructor = $reflectionClass->getConstructor();
            if (!$constructor) {
                $this->fail('Class ' . $instanceName . ' does not have __constructor');
            }

            $parameters = $parameters['parameters'];
            $classParameters = $constructor->getParameters();
            foreach ($classParameters as $classParameter) {
                $parameterName = $classParameter->getName();
                if (array_key_exists($parameterName, $parameters)) {
                    unset($parameters[$parameterName]);
                }
            }
            $message = 'Configuration of ' . $instanceName . ' contains data for non-existed parameters: ' . implode(
                ', ',
                array_keys($parameters)
            );
            $this->assertEmpty($parameters, $message);
        }
    }

    /**
     * Checks if class is a real one or generated Factory
     * @param string $instanceName class name
     * @throws \PHPUnit_Framework_AssertionFailedError
     * @return bool
     */
    protected function _classExistsAsReal($instanceName)
    {
        if (class_exists($instanceName)) {
            return true;
        }
        // check for generated factory
        if (substr($instanceName, -7) == 'Factory' && class_exists(substr($instanceName, 0, -7))) {
            return false;
        }
        $this->fail('Detected configuration of non existed class: ' . $instanceName);
    }

    /**
     * Get php classes list
     *
     * @return array
     */
    protected function _phpClassesDataProvider()
    {
        $basePath = \Magento\TestFramework\Utility\Files::init()->getPathToSource();

        $basePath = str_replace('/', '\\', $basePath);
        $libPath = $basePath . '\\lib';
        $appPath = $basePath . '\\app\\code';
        $generationPathPath = str_replace('/', '\\', $this->_generationDir);

        $files = \Magento\TestFramework\Utility\Files::init()->getClassFiles(
            true,
            false,
            false,
            false,
            false,
            true,
            false
        );

        $patterns = array(
            '/' . preg_quote($libPath) . '/',
            '/' . preg_quote($appPath) . '/',
            '/' . preg_quote($generationPathPath) . '/'
        );
        $replacements = array('', '', '');

        /** Convert file names into class name format */
        $classes = array();
        foreach ($files as $file) {
            $file = str_replace('/', '\\', $file);
            $filePath = preg_replace($patterns, $replacements, $file);
            $className = substr($filePath, 0, -4);
            if (class_exists($className)) {
                $classes[$file] = $className;
            }
        }

        /** Build class inheritance hierarchy  */
        $output = array();
        $allowedFiles = array_keys($classes);
        foreach ($classes as $class) {
            if (!in_array($class, $output)) {
                $output = array_merge($output, $this->_buildInheritanceHierarchyTree($class, $allowedFiles));
                $output = array_unique($output);
            }
        }

        /** Convert data into data provider format */
        $outputClasses = array();
        foreach ($output as $className) {
            $outputClasses[] = array($className);
        }
        return $outputClasses;
    }

    /**
     * Build inheritance hierarchy tree
     *
     * @param string $className
     * @param array $allowedFiles
     * @return array
     */
    protected function _buildInheritanceHierarchyTree($className, array $allowedFiles)
    {
        $output = array();
        if (0 !== strpos($className, '\\')) {
            $className = '\\' . $className;
        }
        $class = new \ReflectionClass($className);
        $parent = $class->getParentClass();
        /** Prevent analysis of non Magento classes  */
        if ($parent && in_array($parent->getFileName(), $allowedFiles)) {
            $output = array_merge(
                $this->_buildInheritanceHierarchyTree($parent->getName(), $allowedFiles),
                array($className),
                $output
            );
        } else {
            $output[] = $className;
        }
        return array_unique($output);
    }

    /**
     * Validate class
     *
     * @param string $className
     */
    protected function _validateClass($className)
    {
        try {
            $this->_validator->validate($className);
        } catch (\Magento\Code\ValidationException $exceptions) {
            $this->fail($exceptions->getMessage());
        } catch (\ReflectionException $exceptions) {
            $this->fail($exceptions->getMessage());
        }
    }

    /**
     * Validate DI configuration
     */
    public function testConfigurationOfInstanceParameters()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $this->_validateFile($file);
            },
            \Magento\TestFramework\Utility\Files::init()->getDiConfigs(true)
        );
    }

    /**
     * Test consistency of plugin interfaces
     */
    public function testPluginInterfaces()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($plugin, $type) {
                $this->validatePlugins($plugin, $type);
            },
            $this->pluginDataProvider()
        );
    }

    /**
     * Validate constructor integrity
     */
    public function testConstructorIntegrity()
    {
        $autoloader = new \Magento\Autoload\IncludePath();
        $generatorIo = new \Magento\Code\Generator\Io(
            new \Magento\Filesystem\Driver\File(),
            $autoloader,
            $this->_generationDir
        );
        $generator = new \Magento\Code\Generator(
            $autoloader,
            $generatorIo,
            array(
                \Magento\ObjectManager\Code\Generator\Factory::ENTITY_TYPE
                => 'Magento\ObjectManager\Code\Generator\Factory'
            )
        );
        $autoloader = new \Magento\Code\Generator\Autoloader($generator);
        spl_autoload_register(array($autoloader, 'load'));

        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($className) {
                $this->_validateClass($className);
            },
            $this->_phpClassesDataProvider()
        );
        spl_autoload_unregister(array($autoloader, 'load'));
    }

    /**
     * Validate plugin interface
     *
     * @param string $plugin
     * @param string $type
     */
    protected function validatePlugins($plugin, $type)
    {
        try {
            $module = \Magento\TestFramework\Utility\Classes::getClassModuleName($type);
            if (\Magento\TestFramework\Utility\Files::init()->isModuleExists($module)) {
                $this->pluginValidator->validate($plugin, $type);
            }
        } catch (\Magento\Interception\Code\ValidatorException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Get application plugins
     *
     * @return array
     */
    protected function pluginDataProvider()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getDiConfigs();
        $plugins = array();
        foreach ($files as $file) {
            $dom = new \DOMDocument();
            $dom->load($file);
            $xpath = new \DOMXPath($dom);
            $pluginList = $xpath->query('//config/type/plugin');
            foreach ($pluginList as $node) {
                /** @var $node \DOMNode */
                $type = $node->parentNode->attributes->getNamedItem('name')->nodeValue;
                $type = \Magento\TestFramework\Utility\Classes::resolveVirtualType($type);
                if ($node->attributes->getNamedItem('type')) {
                    $plugin = $node->attributes->getNamedItem('type')->nodeValue;
                    $plugin = \Magento\TestFramework\Utility\Classes::resolveVirtualType($plugin);
                    $plugins[] = array('plugin' => $plugin, 'intercepted type' => $type);
                }
            }
        }

        return $plugins;
    }

    /**
     * Test DI compiler
     *
     * @depends testConfigurationOfInstanceParameters
     * @depends testConstructorIntegrity
     * @depends testPluginInterfaces
     */
    public function testCompiler()
    {
        try {
            $this->_shell->execute($this->_command, array($this->_generationDir, $this->_compilationDir));
        } catch (\Magento\Exception $exception) {
            $this->fail($exception->getPrevious()->getMessage());
        }
    }
}
