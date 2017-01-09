<?php
/**
 * Compiler test. Check compilation of DI definitions and code generation
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Di;

use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Interception\Code\InterfaceValidator;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Integrity\PluginValidator;

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
     * @var \Magento\Framework\Shell
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
     * @var \Magento\Framework\ObjectManager\Config\Mapper\Dom()
     */
    protected $_mapper;

    /**
     * @var \Magento\Framework\Code\Validator
     */
    protected $_validator;

    /**
     * Class arguments reader
     *
     * @var PluginValidator
     */
    protected $pluginValidator;

    protected function setUp()
    {
        $this->_shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer());
        $basePath = BP;
        $basePath = str_replace('\\', '/', $basePath);

        $directoryList = new DirectoryList($basePath);
        $this->_generationDir = $directoryList->getPath(DirectoryList::GENERATION);
        $this->_compilationDir = $directoryList->getPath(DirectoryList::DI);

        $this->_command = 'php ' . $basePath . '/bin/magento setup:di:compile';

        $booleanUtils = new \Magento\Framework\Stdlib\BooleanUtils();
        $constInterpreter = new \Magento\Framework\Data\Argument\Interpreter\Constant();
        $argumentInterpreter = new \Magento\Framework\Data\Argument\Interpreter\Composite(
            [
                'boolean' => new \Magento\Framework\Data\Argument\Interpreter\Boolean($booleanUtils),
                'string' => new \Magento\Framework\Data\Argument\Interpreter\StringUtils($booleanUtils),
                'number' => new \Magento\Framework\Data\Argument\Interpreter\Number(),
                'null' => new \Magento\Framework\Data\Argument\Interpreter\NullType(),
                'object' => new \Magento\Framework\Data\Argument\Interpreter\DataObject($booleanUtils),
                'const' => $constInterpreter,
                'init_parameter' => new \Magento\Framework\App\Arguments\ArgumentInterpreter($constInterpreter),
            ],
            \Magento\Framework\ObjectManager\Config\Reader\Dom::TYPE_ATTRIBUTE
        );
        // Add interpreters that reference the composite
        $argumentInterpreter->addInterpreter(
            'array',
            new \Magento\Framework\Data\Argument\Interpreter\ArrayType($argumentInterpreter)
        );

        $this->_mapper = new \Magento\Framework\ObjectManager\Config\Mapper\Dom(
            $argumentInterpreter,
            $booleanUtils,
            new \Magento\Framework\ObjectManager\Config\Mapper\ArgumentParser()
        );
        $this->_validator = new \Magento\Framework\Code\Validator();
        $this->_validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $this->_validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());
        $this->_validator->add(new \Magento\Framework\Code\Validator\TypeDuplication());
        $this->_validator->add(new \Magento\Framework\Code\Validator\ArgumentSequence());
        $this->_validator->add(new \Magento\Framework\Code\Validator\ConstructorArgumentTypes());
        $this->pluginValidator = new PluginValidator(new InterfaceValidator());
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
            if (\Magento\Framework\App\Utility\Classes::isVirtual($instanceName)) {
                $instanceName = \Magento\Framework\App\Utility\Classes::resolveVirtualType($instanceName);
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
        $generationPath = str_replace('/', '\\', $this->_generationDir);

        $files = Files::init()->getPhpFiles(Files::INCLUDE_APP_CODE | Files::INCLUDE_LIBS);

        $patterns = ['/' . preg_quote($generationPath) . '/',];
        $replacements = [''];

        $componentRegistrar = new ComponentRegistrar();
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleName => $modulePath) {
            $patterns[] = '/' . preg_quote(str_replace('/', '\\', $modulePath)) . '/';
            $replacements[] = '\\' . str_replace('_', '\\', $moduleName);
        }

        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libPath) {
            $patterns[] = '/' . preg_quote(str_replace('/', '\\', $libPath)) . '/';
            $replacements[] = '\\Magento\\Framework';
        }

        /** Convert file names into class name format */
        $classes = [];
        foreach ($files as $file) {
            $file = str_replace('/', '\\', $file);
            $filePath = preg_replace($patterns, $replacements, $file);
            $className = substr($filePath, 0, -4);
            if (class_exists($className, false)) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
                $classes[$file] = $className;
            }
        }

        /** Build class inheritance hierarchy  */
        $output = [];
        $allowedFiles = array_keys($classes);
        foreach ($classes as $class) {
            if (!in_array($class, $output)) {
                $output = array_merge($output, $this->_buildInheritanceHierarchyTree($class, $allowedFiles));
                $output = array_unique($output);
            }
        }

        /** Convert data into data provider format */
        $outputClasses = [];
        foreach ($output as $className) {
            $outputClasses[] = [$className];
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
        $output = [];
        if (0 !== strpos($className, '\\')) {
            $className = '\\' . $className;
        }
        $class = new \ReflectionClass($className);
        $parent = $class->getParentClass();
        $file = false;
        if ($parent) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $parent->getFileName());
        }
        /** Prevent analysis of non Magento classes  */
        if ($parent && in_array($file, $allowedFiles)) {
            $output = array_merge(
                $this->_buildInheritanceHierarchyTree($parent->getName(), $allowedFiles),
                [$className],
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
        } catch (\Magento\Framework\Exception\ValidatorException $exceptions) {
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
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($file) {
                $this->_validateFile($file);
            },
            Files::init()->getDiConfigs(true)
        );
    }

    /**
     * Validate constructor integrity
     */
    public function testConstructorIntegrity()
    {
        $generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $this->_generationDir
        );
        $generator = new \Magento\Framework\Code\Generator(
            $generatorIo,
            [
                Factory::ENTITY_TYPE => \Magento\Framework\ObjectManager\Code\Generator\Factory::class,
                Repository::ENTITY_TYPE => \Magento\Framework\ObjectManager\Code\Generator\Repository::class,
                Converter::ENTITY_TYPE => \Magento\Framework\ObjectManager\Code\Generator\Converter::class,
                Mapper::ENTITY_TYPE => \Magento\Framework\Api\Code\Generator\Mapper::class,
                SearchResults::ENTITY_TYPE => \Magento\Framework\Api\Code\Generator\SearchResults::class,
                ExtensionAttributesInterfaceGenerator::ENTITY_TYPE =>
                    \Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator::class,
                ExtensionAttributesGenerator::ENTITY_TYPE =>
                    \Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator::class
            ]
        );
        $generationAutoloader = new \Magento\Framework\Code\Generator\Autoloader($generator);
        spl_autoload_register([$generationAutoloader, 'load']);

        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($className) {
                $this->_validateClass($className);
            },
            $this->_phpClassesDataProvider()
        );
        spl_autoload_unregister([$generationAutoloader, 'load']);
    }

    /**
     * Test consistency of plugin interfaces
     */
    public function testPluginInterfaces()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($plugin, $type) {
                $this->validatePlugins($plugin, $type);
            },
            $this->pluginDataProvider()
        );
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
            $module = \Magento\Framework\App\Utility\Classes::getClassModuleName($type);
            if (Files::init()->isModuleExists($module)) {
                $this->pluginValidator->validate($plugin, $type);
            }
        } catch (\Magento\Framework\Exception\ValidatorException $exception) {
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
        $files = Files::init()->getDiConfigs();
        $plugins = [];
        foreach ($files as $file) {
            $dom = new \DOMDocument();
            $dom->load($file);
            $xpath = new \DOMXPath($dom);
            $pluginList = $xpath->query('//config/type/plugin');
            foreach ($pluginList as $node) {
                /** @var $node \DOMNode */
                $type = $node->parentNode->attributes->getNamedItem('name')->nodeValue;
                $type = \Magento\Framework\App\Utility\Classes::resolveVirtualType($type);
                if ($node->attributes->getNamedItem('type')) {
                    $plugin = $node->attributes->getNamedItem('type')->nodeValue;
                    $plugin = \Magento\Framework\App\Utility\Classes::resolveVirtualType($plugin);
                    $plugins[] = ['plugin' => $plugin, 'intercepted type' => $type];
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
        $this->markTestSkipped('MAGETWO-52570');
        try {
            $this->_shell->execute($this->_command);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->fail($exception->getPrevious()->getMessage());
        }
    }
}
