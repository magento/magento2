<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Code\Generator\DefinedClasses;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Dto\DtoConfig;
use Magento\Framework\Dto\DtoProcessor;
use Magento\Framework\Dto\DtoProjection;
use Magento\Framework\ObjectManagerInterface;

class Factory extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'factory';

    /**
     * @var DtoConfig
     */
    private $dtoConfig;

    /**
     * @var null
     */
    private $sourceClassName;

    public function __construct(
        DtoConfig $dtoConfig,
        $sourceClassName = null,
        $resultClassName = null,
        Io $ioObject = null,
        CodeGeneratorInterface $classGenerator = null,
        DefinedClasses $definedClasses = null
    ) {
        $this->dtoConfig = $dtoConfig;
        $this->sourceClassName = $sourceClassName;

        parent::__construct(
            $sourceClassName,
            $resultClassName,
            $ioObject,
            $classGenerator,
            $definedClasses
        );
    }

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = parent::_getClassProperties();

        // protected $_instanceName = null;
        $properties[] = [
            'name' => '_instanceName',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Instance name to create',
                'tags' => [['name' => 'var', 'description' => 'string']],
            ],
        ];
        return $properties;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                ['name' => 'objectManager', 'type' => '\\' . ObjectManagerInterface::class],
                ['name' => 'instanceName', 'defaultValue' => $this->getSourceClassName()],
            ],
            'body' => "\$this->_objectManager = \$objectManager;\n\$this->_instanceName = \$instanceName;",
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\ObjectManagerInterface $objectManager',
                    ],
                    ['name' => 'param', 'description' => 'string $instanceName'],
                ],
            ]
        ];
    }

    /**
     * Get create method for DTO
     *
     * @return array
     */
    private function getCreateMethodForDto(): array
    {
        return [
            'name' => 'create',
            'parameters' => [['name' => 'data', 'type' => 'array', 'defaultValue' => []]],
            'body' => '$dtoProcessor = $this->_objectManager->get(\\' . DtoProcessor::class . '::class);' . "\n"
                . 'return $dtoProcessor->createFromArray($data, $this->_instanceName);',
            'returnType' => $this->getSourceClassName(),
            'docblock' => [
                'shortDescription' => 'Create class instance with specified parameters',
                'tags' => [
                    ['name' => 'param', 'description' => 'array $data'],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName()
                    ],
                ],
            ],
        ];
    }

    /**
     * Get create as projection method for DTO
     *
     * @return array
     */
    private function getCreateAsProjectionMethod(): array
    {
        return [
            'name' => 'createAsProjection',
            'parameters' => [
                ['name' => 'sourceType', 'type' => 'string'],
                ['name' => 'source'],
            ],
            'returnType' => $this->getSourceClassName(),
            'body' => '$dtoProjection = $this->_objectManager->get(\\' . DtoProjection::class . '::class);' . "\n"
                . 'return $dtoProjection->execute($this->_instanceName, $sourceType, $source);',
            'docblock' => [
                'shortDescription' => 'Create DTO instance as projection of an existing object',
                'tags' => [
                    ['name' => 'param', 'description' => 'string $sourceType'],
                    ['name' => 'param', 'description' => 'mixed $source'],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName()
                    ],
                ],
            ],
        ];
    }

    /**
     * Get crete method for standard objects
     *
     * @return array
     */
    private function getStandardCreateMethod(): array
    {
        return [
            'name' => 'create',
            'parameters' => [['name' => 'data', 'type' => 'array', 'defaultValue' => []]],
            'body' => 'return $this->_objectManager->create($this->_instanceName, $data);',
            'docblock' => [
                'shortDescription' => 'Create class instance with specified parameters',
                'tags' => [
                    ['name' => 'param', 'description' => 'array $data'],
                    [
                        'name' => 'return',
                        'description' => $this->getSourceClassName()
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $methods = [$this->_getDefaultConstructorDefinition()];

        if ($this->dtoConfig->isDto($this->getSourceClassName())) {
            $methods[] = $this->getCreateMethodForDto();
            $methods[] = $this->getCreateAsProjectionMethod();
        } else {
            $methods[] = $this->getStandardCreateMethod();
        }

        return $methods;
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . 'Factory') {
                $this->_addError(
                    'Invalid Factory class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Factory'
                );
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function _generateCode()
    {
        $generatedCode = parent::_generateCode();

        // Keep backward compatibility with previous factories
        if ($this->dtoConfig->isDto($this->getSourceClassName())) {
            return 'declare(strict_types=1);' . "\n\n" . $generatedCode;
        }

        return $generatedCode;
    }
}
