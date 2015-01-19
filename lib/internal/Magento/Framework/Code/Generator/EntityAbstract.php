<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

abstract class EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'abstract';

    /**
     * @var string[]
     */
    private $_errors = [];

    /**
     * Source model class name
     *
     * @var string
     */
    private $_sourceClassName;

    /**
     * Result model class name
     *
     * @var string
     */
    private $_resultClassName;

    /**
     * @var Io
     */
    private $_ioObject;

    /**
     * Class generator object
     *
     * @var CodeGenerator\CodeGeneratorInterface
     */
    protected $_classGenerator;

    /**
     * @var DefinedClasses
     */
    private $definedClasses;

    /**
     * @param null|string $sourceClassName
     * @param null|string $resultClassName
     * @param Io $ioObject
     * @param CodeGenerator\CodeGeneratorInterface $classGenerator
     * @param DefinedClasses $definedClasses
     */
    public function __construct(
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        CodeGenerator\CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        if ($ioObject) {
            $this->_ioObject = $ioObject;
        } else {
            $this->_ioObject = new Io(new \Magento\Framework\Filesystem\Driver\File());
        }
        if ($classGenerator) {
            $this->_classGenerator = $classGenerator;
        } else {
            $this->_classGenerator = new CodeGenerator\Zend();
        }
        if ($definedClasses) {
            $this->definedClasses = $definedClasses;
        } else {
            $this->definedClasses = new DefinedClasses();
        }

        $this->_sourceClassName = ltrim($sourceClassName, '\\');
        if ($resultClassName) {
            $this->_resultClassName = $resultClassName;
        } elseif ($sourceClassName) {
            $this->_resultClassName = $this->_getDefaultResultClassName($sourceClassName);
        }
    }

    /**
     * Generation template method
     *
     * @return bool
     */
    public function generate()
    {
        try {
            if ($this->_validateData()) {
                $sourceCode = $this->_generateCode();
                if ($sourceCode) {
                    $fileName = $this->_ioObject->getResultFileName($this->_getResultClassName());
                    $this->_ioObject->writeResultFile($fileName, $sourceCode);
                    return $fileName;
                } else {
                    $this->_addError('Can\'t generate source code.');
                }
            }
        } catch (\Exception $e) {
            $this->_addError($e->getMessage());
        }
        return false;
    }

    /**
     * List of occurred generation errors
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get source class name
     *
     * @return string
     */
    protected function _getSourceClassName()
    {
        return $this->_sourceClassName;
    }

    /**
     * Get fully qualified class name
     *
     * @param string $className
     * @return string
     */
    protected function _getFullyQualifiedClassName($className)
    {
        return '\\' . ltrim($className, '\\');
    }

    /**
     * Get result class name
     *
     * @return string
     */
    protected function _getResultClassName()
    {
        return $this->_resultClassName;
    }

    /**
     * Get default result class name
     *
     * @param string $modelClassName
     * @return string
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * Returns list of properties for class generator
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        // protected $_objectManager = null;
        $objectManager = [
            'name' => '_objectManager',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Object Manager instance',
                'tags' => [['name' => 'var', 'description' => '\Magento\Framework\ObjectManagerInterface']],
            ],
        ];

        return [$objectManager];
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    abstract protected function _getDefaultConstructorDefinition();

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    abstract protected function _getClassMethods();

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName(
            $this->_getResultClassName()
        )->addProperties(
            $this->_getClassProperties()
        )->addMethods(
            $this->_getClassMethods()
        )->setClassDocBlock(
            $this->_getClassDocBlock()
        );

        return $this->_getGeneratedCode();
    }

    /**
     * Add error message
     *
     * @param string $message
     * @return $this
     */
    protected function _addError($message)
    {
        $this->_errors[] = $message;
        return $this;
    }

    /**
     * @return bool
     */
    protected function _validateData()
    {
        $sourceClassName = $this->_getSourceClassName();
        $resultClassName = $this->_getResultClassName();
        $resultFileName = $this->_ioObject->getResultFileName($resultClassName);

        // @todo the controller handling logic below must be removed when controllers become PSR-0 compliant
        $controllerSuffix = 'Controller';
        $pathParts = explode('_', $sourceClassName);
        if (strrpos(
            $sourceClassName,
            $controllerSuffix
        ) === strlen(
            $sourceClassName
        ) - strlen(
            $controllerSuffix
        ) && isset(
            $pathParts[2]
        ) && !in_array(
            $pathParts[2],
            ['Block', 'Helper', 'Model']
        )
        ) {
            $controllerPath = preg_replace(
                '/^([0-9A-Za-z]*)_([0-9A-Za-z]*)/',
                '\\1_\\2_controllers',
                $sourceClassName
            );
            $filePath = stream_resolve_include_path(str_replace('_', '/', $controllerPath) . '.php');
            $isSourceClassValid = !empty($filePath);
        } else {
            $isSourceClassValid = $this->definedClasses->classLoadable($sourceClassName);
        }

        if (!$isSourceClassValid) {
            $this->_addError('Source class ' . $sourceClassName . ' doesn\'t exist.');
            return false;
        } elseif ($this->definedClasses->classLoadable($resultClassName)) {
            $this->_addError('Result class ' . $resultClassName . ' already exists.');
            return false;
        } elseif (!$this->_ioObject->makeGenerationDirectory()) {
            $this->_addError('Can\'t create directory ' . $this->_ioObject->getGenerationDirectory() . '.');
            return false;
        } elseif (!$this->_ioObject->makeResultFileDirectory($resultClassName)) {
            $this->_addError(
                'Can\'t create directory ' . $this->_ioObject->getResultFileDirectory($resultClassName) . '.'
            );
            return false;
        } elseif ($this->_ioObject->fileExists($resultFileName)) {
            $this->_addError('Result file ' . $resultFileName . ' already exists.');
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    protected function _getClassDocBlock()
    {
        $description = ucfirst(static::ENTITY_TYPE) . ' class for \\' . $this->_getSourceClassName();
        return ['shortDescription' => $description];
    }

    /**
     * @return string
     */
    protected function _getGeneratedCode()
    {
        $sourceCode = $this->_classGenerator->generate();
        return $this->_fixCodeStyle($sourceCode);
    }

    /**
     * @param string $sourceCode
     * @return string
     */
    protected function _fixCodeStyle($sourceCode)
    {
        $sourceCode = str_replace(' array (', ' array(', $sourceCode);
        $sourceCode = preg_replace("/{\n{2,}/m", "{\n", $sourceCode);
        $sourceCode = preg_replace("/\n{2,}}/m", "\n}", $sourceCode);
        return $sourceCode;
    }

    /**
     * Get value generator for null default value
     *
     * @return \Zend\Code\Generator\ValueGenerator
     */
    protected function _getNullDefaultValue()
    {
        $value = new \Zend\Code\Generator\ValueGenerator(null, \Zend\Code\Generator\ValueGenerator::TYPE_NULL);

        return $value;
    }

    /**
     * Retrieve method parameter info
     *
     * @param \ReflectionParameter $parameter
     * @return array
     */
    protected function _getMethodParameterInfo(\ReflectionParameter $parameter)
    {
        $parameterInfo = [
            'name' => $parameter->getName(),
            'passedByReference' => $parameter->isPassedByReference(),
        ];

        if ($parameter->isArray()) {
            $parameterInfo['type'] = 'array';
        } elseif ($parameter->getClass()) {
            $parameterInfo['type'] = $this->_getFullyQualifiedClassName($parameter->getClass()->getName());
        }

        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            $defaultValue = $parameter->getDefaultValue();
            if (is_string($defaultValue)) {
                $parameterInfo['defaultValue'] = $parameter->getDefaultValue();
            } elseif ($defaultValue === null) {
                $parameterInfo['defaultValue'] = $this->_getNullDefaultValue();
            } else {
                $parameterInfo['defaultValue'] = $defaultValue;
            }
        }

        return $parameterInfo;
    }

    /**
     * Reinit generator
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return void
     */
    public function init($sourceClassName, $resultClassName)
    {
        $this->_sourceClassName = $sourceClassName;
        $this->_resultClassName = $resultClassName;
    }
}
