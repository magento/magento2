<?php
/**
 * DTO mutator generator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Code\Generator;

use Magento\Framework\Api\DtoProcessor;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Code\Generator\EntityAbstract;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Mutator
 *
 * @package Magento\Framework\ObjectManager\Code\Generator
 */
class Mutator extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'mutator';

    /**
     * @inheritDoc
     */
    protected function _getClassProperties()
    {
        $dtoProcessor = [
            'name' => 'dtoProcessor',
            'visibility' => 'private',
            'docblock' => [
                'shortDescription' => 'Dto Processor',
                'tags' => [
                    [
                        'name' => 'var',
                        'description' => '\\' . DtoProcessor::class
                    ]
                ],
            ],
        ];

        $data = [
            'name' => 'data',
            'visibility' => 'private',
            'defaultValue' => [],
            'docblock' => [
                'shortDescription' => 'Mutator data storage',
                'tags' => [
                    [
                        'name' => 'var',
                        'description' => 'array'
                    ]
                ],
            ],
        ];

        return [$dtoProcessor, $data];
    }

    /**
     * @inheritDoc
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                [
                    'name' => 'dtoProcessor',
                    'type' => DtoProcessor::class,
                ],
            ],
            'body' => '$this->dtoProcessor = $dtoProcessor;',
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => DtoProcessor::class . ' $dtoProcessor',
                    ],
                ]
            ]
        ];
    }

    /**
     * Get mutate method
     *
     * @return array
     */
    private function getMutateMethod(): array
    {
        return [
            'name' => 'mutate',
            'parameters' => [
                [
                    'name' => 'sourceObject',
                    'type' => $this->getSourceClassName(),
                ],
            ],
            'body' => '$res = $this->dtoProcessor->createUpdatedObjectFromArray($sourceObject, $this->data);' . "\n" .
                '$this->data = [];' . "\n" .
                'return $res;',
            'returnType' => $this->getSourceClassName(),
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'sourceObject',
                        'description' => $this->getSourceClassName(),
                    ],
                ]
            ]
        ];
    }

    /**
     * @param string $propertyCamelCase
     * @param ReflectionMethod $method
     * @return array
     */
    private function generateWithMethod(string $propertyCamelCase, ReflectionMethod $method): array
    {
        $snakeCaseName = SimpleDataObjectConverter::camelCaseToSnakeCase($propertyCamelCase);
        $docBlockType = $method->getReturnType();
        $valueType = $method->getReturnType();
        $mutatorClassName = $this->getSourceClassName() . 'Mutator';

        return [
            'name' => 'with' . $propertyCamelCase,
            'parameters' => [
                [
                    'name' => 'value',
                    'type' => $valueType
                ]
            ],
            'returnType' => $mutatorClassName,
            'body' => "\$this->data['" . $snakeCaseName . "'] = \$value;\nreturn \$this;",
            'docblock' => [
                'shortDescription' => 'Mutator for ' . $snakeCaseName,
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => $docBlockType . ' $value'
                    ],
                    [
                        'name' => 'return',
                        'description' => $mutatorClassName
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    protected function _getClassMethods()
    {
        $generatedMethods = [$this->_getDefaultConstructorDefinition()];

        $typeName = $this->getSourceClassName();
        $reflection = new ReflectionClass($typeName);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getNumberOfRequiredParameters() === 0 &&
                preg_match('/^(is|get)([A-Z]\w*)$/', $method->getName(), $matches)
            ) {
                $camelCaseName = $matches[2];
                $generatedMethods[] = $this->generateWithMethod($camelCaseName, $method);
            }
        }

        $generatedMethods[] = $this->getMutateMethod();

        return $generatedMethods;
    }

    /**
     * @inheritDoc
     */
    protected function _generateCode()
    {
        return 'declare(strict_types=1);' . "\n\n" . parent::_generateCode();
    }
}
