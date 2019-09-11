<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Code;

use LogicException;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ImmutableExtensibleDataInterface;
use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\InterfaceGenerator;
use Magento\Framework\Dto\DtoConfig;
use Magento\Framework\Reflection\TypeProcessor;

/**
 * Dto Source code generator class
 */
class GetDtoSourceCode
{
    /**
     * @var DtoConfig
     */
    private $dtoConfig;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * @param DtoConfig $dtoConfig
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(
        DtoConfig $dtoConfig,
        TypeProcessor $typeProcessor
    ) {
        $this->dtoConfig = $dtoConfig;
        $this->typeProcessor = $typeProcessor;
    }

    /**
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
     * @param string $className
     * @return string
     */
    public function execute(string $className): string
    {
        if (!$this->dtoConfig->isDto($className)) {
            throw new LogicException('Unknown DTO ' . $className);
        }

        $config = $this->dtoConfig->get($className);
        $config = $this->injectExtensionAttributes($className, $config);

        if ($config['type'] === 'interface') {
            return $this->generateInterfaceSource($className, $config);
        }

        return $this->generateDtoSource($config['interface'], $className, $config);
    }

    /**
     * @param string $interfaceName
     * @param string $className
     * @param array $config
     * @return string
     */
    private function generateDtoSource(string $interfaceName, string $className, array $config): string
    {
        $constructor = $this->getConstructor($config);

        $methods = [];
        if ($constructor !== null) {
            $methods[] = $constructor;
        }

        $methods = array_merge($methods, $this->getClassMethods($interfaceName, $config));

        /** @var ClassGenerator $classGenerator */
        $classGenerator = new ClassGenerator();
        $classGenerator->setImplementedInterfaces([$config['interface']]);
        $classGenerator->setName($className);
        $classGenerator->addProperties($this->getClassProperties($config));
        $classGenerator->addMethods($methods);

        return 'declare(strict_types=1);' . "\n\n" . $this->fixCodeStyle($classGenerator->generate());
    }

    /**
     * @param string|null $interfaceName
     * @param array $config
     * @return string|null
     */
    private function generateInterfaceSource(?string $interfaceName, array $config): ?string
    {
        if (empty($interfaceName)) {
            return null;
        }

        $methods = $this->getClassMethods($interfaceName, $config);
        foreach ($methods as &$method) {
            unset($method['body']);
        }
        unset($method);

        /** @var InterfaceGenerator $interfaceGenerator */
        $interfaceGenerator = new InterfaceGenerator();
        $interfaceGenerator->setName($interfaceName);
        $interfaceGenerator->addMethods($methods);

        if ($config['mutable']) {
            $interfaceGenerator->setExtendedClass(ExtensibleDataInterface::class);
        } else {
            $interfaceGenerator->setExtendedClass(ImmutableExtensibleDataInterface::class);
        }

        return 'declare(strict_types=1);' . "\n\n" . $this->fixCodeStyle($interfaceGenerator->generate());
    }

    /**
     * @param string $className
     * @param array $config
     * @return array
     */
    private function injectExtensionAttributes(string $className, array $config): array
    {
        $interface = $config['type'] === 'class' ? $config['interface'] : $className;
        $interface = preg_replace('/Interface$/', '', $interface);

        $config['properties']['extensionAttributes'] = [
            'type' => $interface . 'ExtensionInterface',
            'optional' => true,
            'nullable' => true
        ];

        return $config;
    }

    /**
     * @param array $config
     * @return array
     */
    private function getClassProperties(array $config): array
    {
        $properties = [];
        foreach ($config['properties'] as $propertyName => $propertyMetadata) {
            $attributeDescriptiveType = $this->getDescriptiveType($propertyMetadata['type']);

            $properties[] = [
                'name' => $propertyName,
                'visibility' => 'private',
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'var',
                            'description' => $attributeDescriptiveType
                        ]
                    ],
                ]
            ];
        }

        return $properties;
    }

    /**
     * @param array $config
     * @return array
     */
    private function getConstructor(array $config): ?array
    {
        $parametersByType = [
            'required' => [],
            'optional' => []
        ];
        $body = [];
        $tags = [];

        foreach ($config['properties'] as $propertyName => $propertyMetadata) {
            $type = $propertyMetadata['optional'] ? 'optional' : 'required';

            $attributeRealType = $this->getRealType($propertyMetadata['type']);
            $attributeDescriptiveType = $this->getDescriptiveType($propertyMetadata['type']);

            $parametersByType[$type][] = [
                'name' => $propertyName . ($propertyMetadata['optional'] ? ' = null' : ''),
                'type' => ($propertyMetadata['nullable'] || $propertyMetadata['optional'] ? '?' : '') . $attributeRealType
            ];

            $body[] = '$this->' . $propertyName . ' = $' . $propertyName . ';';
            $tags[] = [
                'name' => 'param',
                'description' => $attributeDescriptiveType . ($propertyMetadata['nullable'] ? '|null ' : ' ') . '$' . $propertyName,
            ];
        }

        $parameters = array_merge($parametersByType['required'], $parametersByType['optional']);

        if (!$parameters) {
            return null;
        }

        return [
            'name' => '__construct',
            'parameters' => $parameters,
            'body' => implode("\n", $body),
            'docblock' => [
                'tags' => $tags
            ]
        ];
    }

    /**
     * @param array $config
     * @return array
     */
    private function getGetterMethods(array $config): array
    {
        $methods = [];

        foreach ($config['properties'] as $propertyName => $propertyMetadata) {
            $attributeRealType = $this->getRealType($propertyMetadata['type']);
            $attributeDescriptiveType = $this->getDescriptiveType($propertyMetadata['type']);

            $getterName = 'get' . ucfirst($propertyName);

            $nullable = $propertyMetadata['nullable'] || $propertyMetadata['optional'];

            $methods[] = [
                'name' => $getterName,
                'body' => "return \$this->$propertyName;",
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'return',
                            'description' => $attributeDescriptiveType . ($nullable ? '|null' : '')
                        ]
                    ]
                ],
                'returnType' => ($nullable ? '?' : '') . $attributeRealType
            ];
        }

        return $methods;
    }

    /**
     * @param array $config
     * @return array
     */
    private function getSetterMethods(array $config): array
    {
        $methods = [];

        foreach ($config['properties'] as $propertyName => $propertyMetadata) {
            $attributeRealType = $this->getRealType($propertyMetadata['type']);
            $attributeDescriptiveType = $this->getDescriptiveType($propertyMetadata['type']);

            $setterName = 'set' . ucfirst($propertyName);

            $parameters = [
                [
                    'name' => 'value',
                    'type' => ($propertyMetadata['nullable'] ? '?' : '') . $attributeRealType
                ]
            ];

            $methods[] = [
                'name' => $setterName,
                'parameters' => $parameters,
                'body' => "\$this->$propertyName = \$value;",
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'param',
                            'description' => $attributeDescriptiveType . ($propertyMetadata['nullable'] ? '|null ' : ' ') . '$value',
                        ],
                        [
                            'name' => 'return',
                            'description' => 'void'
                        ]
                    ]
                ],
                'returnType' => 'void'
            ];
        }

        return $methods;
    }

    /**
     * @param string $interfaceName
     * @param array $config
     * @return array
     */
    private function getWithMethods(string $interfaceName, array $config): array
    {
        $methods = [];

        foreach ($config['properties'] as $propertyName => $propertyMetadata) {
            $attributeRealType = $this->getRealType($propertyMetadata['type']);
            $attributeDescriptiveType = $this->getDescriptiveType($propertyMetadata['type']);

            $withName = 'with' . ucfirst($propertyName);

            $parameters = [
                [
                    'name' => 'value',
                    'type' => ($propertyMetadata['nullable'] ? '?' : '') . $attributeRealType
                ]
            ];

            $methods[] = [
                'name' => $withName,
                'parameters' => $parameters,
                'body' => '$dtoProcessor = \Magento\Framework\App\ObjectManager::getInstance()'
                    . '->get(\Magento\Framework\Dto\DtoProcessor::class);' . "\n"
                    . "return \$dtoProcessor->createUpdatedObjectFromArray(\$this, ['$propertyName' => \$value]);"
                    ,
                'docblock' => [
                    'tags' => [
                        [
                            'name' => 'param',
                            'description' => $attributeDescriptiveType . ($propertyMetadata['nullable'] ? '|null ' : ' ') . '$value',
                        ],
                        [
                            'name' => 'return',
                            'description' => $interfaceName
                        ]
                    ]
                ],
                'returnType' => $interfaceName
            ];
        }

        return $methods;
    }

    /**
     * @param string $interfaceName
     * @param array $config
     * @return array
     */
    private function getClassMethods(string $interfaceName, array $config): array
    {
        $methods = $this->getGetterMethods($config);
        if ($config['mutable']) {
            $methods = array_merge($methods, $this->getSetterMethods($config));
        } else {
            $methods = array_merge($methods, $this->getWithMethods($interfaceName, $config));
        }

        return $methods;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getRealType(string $type): string
    {
        return $this->typeProcessor->isArrayType($type) ? 'array' : $type;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getDescriptiveType(string $type): string
    {
        if ($type[0] === strtoupper($type[0])) {
            return '\\' . $type;
        }

        return $type;
    }
}
