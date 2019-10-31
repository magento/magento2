<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Code;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\CodeGeneratorInterface;
use Magento\Framework\Dto\DtoProcessor;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Get the mutator source code
 */
class GetMutatorSourceCode
{
    /**
     * @var CodeGeneratorInterface
     */
    private $classGenerator;

    /**
     * @param ClassGenerator|null $classGenerator
     */
    public function __construct(
        ClassGenerator $classGenerator = null
    ) {
        $this->classGenerator = $classGenerator ?: new ClassGenerator();
    }

    /**
     * Fix code style
     *
     * @param string $sourceCode
     * @return string
     */
    private function fixCodeStyle($sourceCode): string
    {
        $sourceCode = preg_replace("/{\n{2,}/m", "{\n", $sourceCode);
        $sourceCode = preg_replace("/\n{2,}}/m", "\n}", $sourceCode);
        return $sourceCode;
    }

    /**
     * Returns the generated code
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return string
     * @throws ReflectionException
     */
    public function execute(string $sourceClassName, string $resultClassName): string
    {
        $methods = $this->getClassMethods($sourceClassName, $resultClassName);

        $this->classGenerator
            ->setName($resultClassName)
            ->addProperties($this->getClassProperties())
            ->addMethods($methods);

        return 'declare(strict_types=1);' . "\n\n" . $this->fixCodeStyle($this->classGenerator->generate());
    }

    /**
     * Returns class properties for code generation
     *
     * @return array
     */
    private function getClassProperties(): array
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
     * Get the constructor for code generation
     *
     * @return array
     */
    private function getConstructor(): array
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
                'shortDescription' => 'Mutator constructor',
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
     * Get mutate method for code generation
     *
     * @param string $sourceClassName
     * @return array
     */
    private function getMutateMethod(string $sourceClassName): array
    {
        return [
            'name' => 'mutate',
            'parameters' => [
                [
                    'name' => 'sourceObject',
                    'type' => $sourceClassName,
                ],
            ],
            'body' => '$res = $this->dtoProcessor->createUpdatedObjectFromArray($sourceObject, $this->data);' . "\n" .
                '$this->data = [];' . "\n" .
                'return $res;',
            'returnType' => $sourceClassName,
            'docblock' => [
                'shortDescription' => 'Mutator constructor',
                'tags' => [
                    [
                        'name' => 'sourceObject',
                        'description' => $sourceClassName,
                    ],
                ]
            ]
        ];
    }

    /**
     * Create PSR-7 like with methods
     *
     * @param string $resultClassName
     * @param string $propertyCamelCase
     * @param ReflectionMethod $method
     * @return array
     */
    private function generateWithMethods(
        string $resultClassName,
        string $propertyCamelCase,
        ReflectionMethod $method
    ): array {
        $snakeCaseName = SimpleDataObjectConverter::camelCaseToSnakeCase($propertyCamelCase);
        $docBlockType = $method->getReturnType();
        $valueType = $method->getReturnType();

        return [
            'name' => 'with' . $propertyCamelCase,
            'parameters' => [
                [
                    'name' => 'value',
                    'type' => $valueType
                ]
            ],
            'returnType' => $resultClassName,
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
                        'description' => $resultClassName
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the whole methods list for code generation
     *
     * @param string $sourceClassName
     * @param string $resultClassName
     * @return array
     * @throws ReflectionException
     */
    private function getClassMethods(string $sourceClassName, string $resultClassName): array
    {
        $generatedMethods = [$this->getConstructor()];

        $reflection = new ReflectionClass($sourceClassName);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getNumberOfRequiredParameters() === 0 &&
                preg_match('/^(is|get)([A-Z]\w*)$/', $method->getName(), $matches)
            ) {
                $camelCaseName = $matches[2];
                $generatedMethods[] = $this->generateWithMethods(
                    $resultClassName,
                    $camelCaseName,
                    $method
                );
            }
        }

        $generatedMethods[] = $this->getMutateMethod($sourceClassName);

        return $generatedMethods;
    }
}
