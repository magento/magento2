<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

class GraphQlReader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * File locator
     *
     * @var \Magento\Framework\Config\FileResolverInterface
     */
    protected $fileResolver;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    protected $defaultScope;

    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        $fileName = 'schema.graphql',
        $defaultScope = 'global'
    ) {
        $this->fileResolver = $fileResolver;
        $this->defaultScope = $defaultScope;
        $this->fileName = $fileName;
    }

    public function read($scope = null)
    {
        $result = [];
        $scope = $scope ?: $this->defaultScope;
        $schemaFiles = $this->fileResolver->get($this->fileName, $scope);
        if (!count($schemaFiles)) {
            return $result;
        }

        foreach ($schemaFiles as $schemaContent) {
            $partialResult = [];
            $schema = \GraphQL\Utils\BuildSchema::build($schemaContent);
            $typeMap = $schema->getTypeMap();
            foreach ($typeMap as $typeName => $typeMeta) {
                if (strpos($typeName, '__') === 0) {
                    // Skip built-in object types
                    continue;
                }

                if ($typeMeta instanceof \GraphQL\Type\Definition\ScalarType) {
                    // Skip built-in scalar types
                    continue;
                }

                // TODO: Use polymorphism instead
                if ($typeMeta instanceof \GraphQL\Type\Definition\EnumType) {
                    $partialResult[$typeName] = $this->readEnumTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\ObjectType) {
                    $partialResult[$typeName] = $this->readObjectTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\InputObjectType) {
                    $partialResult[$typeName] = $this->readInputObjectTypeMeta($typeMeta);
                    continue;
                }
                if ($typeMeta instanceof \GraphQL\Type\Definition\InterfaceType) {
                    $partialResult[$typeName] = $this->readInterfaceTypeMeta($typeMeta);
                    continue;
                }
                // TODO: This is necessary to catch unprocessed GraphQL types, like unions if the will be used in schema
                throw new \LogicException("'{$typeName}' cannot be processed.");
            }
            $result = array_replace_recursive($result, $partialResult);
        }

        return $result;
    }


    private function readEnumTypeMeta(\GraphQL\Type\Definition\EnumType $typeMeta)
    {
        $result = [
            'name' => $typeMeta->name,
            'type' => 'graphql_enum',
            'items' => [] // Populated later
        ];
        foreach ($typeMeta->getValues() as $value) {
            // TODO: Simplify structure, currently name is lost during conversion to GraphQL schema
            $result['items'][$value->value] = [
                'name' => strtolower($value->name),
                '_value' => $value->value
            ];
        }

        return $result;
    }

    private function isScalarType($type)
    {
        return in_array($type, ['String', 'Int', 'Float', 'Boolean', 'ID']);
    }

    private function readObjectTypeMeta(\GraphQL\Type\Definition\ObjectType $typeMeta)
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_type',
            'fields' => [], // Populated later

        ];

        $interfaces = $typeMeta->getInterfaces();
        foreach ($interfaces as $interfaceMeta) {
            $interfaceName = $interfaceMeta->name;
            $result['implements'][$interfaceName] = [
                'interface' => $interfaceName,
                'copyFields' => true // TODO: Configure in separate config
            ];
        }

        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readFieldMeta($fieldMeta);
        }

        return $result;
    }

    private function readInputObjectTypeMeta(\GraphQL\Type\Definition\InputObjectType $typeMeta)
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_input',
            'fields' => [] // Populated later
        ];
        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readInputObjectFieldMeta($fieldMeta);
        }
        return $result;
    }

    private function readInterfaceTypeMeta(\GraphQL\Type\Definition\InterfaceType $typeMeta)
    {
        $typeName = $typeMeta->name;
        $result = [
            'name' => $typeName,
            'type' => 'graphql_interface',
            'fields' => []
        ];

        $interfaceTypeResolver = $this->readInterfaceTypeResolver($typeMeta);
        if ($interfaceTypeResolver) {
            $result['typeResolver'] = $interfaceTypeResolver;
        }

        $fields = $typeMeta->getFields();
        foreach ($fields as $fieldName => $fieldMeta) {
            $result['fields'][$fieldName] = $this->readFieldMeta($fieldMeta);
        }
        return $result;
    }

    private function readFieldMeta(\GraphQL\Type\Definition\FieldDefinition $fieldMeta)
    {
        $fieldName = $fieldMeta->name;
        $fieldTypeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'arguments' => []
        ];

        $fieldResolver = $this->readFieldResolver($fieldMeta);
        if ($fieldResolver) {
            $result['resolver'] = $fieldResolver;
        }

        $result = array_merge(
            $result,
            $this->readTypeMeta($fieldTypeMeta, 'OutputField')
        );

        $arguments = $fieldMeta->args;
        foreach ($arguments as $argumentMeta) {
            $argumentName = $argumentMeta->name;
            $result['arguments'][$argumentName] = [
                'name' => $argumentName,
            ];
            $typeMeta = $argumentMeta->getType();
            $result['arguments'][$argumentName] = array_merge(
                $result['arguments'][$argumentName],
                $this->readTypeMeta($typeMeta, 'Argument')
            );
        }
        return $result;
    }

    private function readInputObjectFieldMeta(\GraphQL\Type\Definition\InputObjectField $fieldMeta)
    {
        $fieldName = $fieldMeta->name;
        $typeMeta = $fieldMeta->getType();
        $result = [
            'name' => $fieldName,
            'required' => false,
            // TODO arguments don't make sense here, but expected to be always present in \Magento\Framework\GraphQl\Config\Data\Mapper\TypeMapper::map
            'arguments' => []
        ];

        $result = array_merge($result, $this->readTypeMeta($typeMeta, 'InputField'));
        return $result;
    }

    /**
     * @param $meta
     * @param string $parameterType Argument|OutputField|InputField
     * @return mixed
     */
    private function readTypeMeta($meta, $parameterType = 'Argument')
    {
        if ($meta instanceof \GraphQL\Type\Definition\NonNull) {
            $result['required'] = true;
            $meta = $meta->getWrappedType();
        } else {
            $result['required'] = false;
        }
        if ($meta instanceof \GraphQL\Type\Definition\ListOfType) {
            $itemTypeMeta = $meta->ofType;
            if ($itemTypeMeta instanceof \GraphQL\Type\Definition\NonNull) {
                $result['itemsRequired'] = true;
                $itemTypeMeta = $itemTypeMeta->getWrappedType();
            } else {
                $result['itemsRequired'] = false;
            }
            $result['description'] = $itemTypeMeta->description;
            $itemTypeName = $itemTypeMeta->name;
            $result['itemType'] = $itemTypeName;
            if ($this->isScalarType($itemTypeMeta)) {
                $result['type'] = 'ScalarArray' . $parameterType;
            } else {
                $result['type'] = 'ObjectArray' . $parameterType;
            }
        } else {
            $result['description'] = $meta->description;
            $result['type'] = $meta->name;
        }
        return $result;
    }

    /**
     * @param \GraphQL\Type\Definition\FieldDefinition $fieldMeta
     * @return string|null
     */
    private function readFieldResolver(\GraphQL\Type\Definition\FieldDefinition $fieldMeta)
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $fieldMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'resolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param \GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta
     * @return string|null
     */
    private function readInterfaceTypeResolver(\GraphQL\Type\Definition\InterfaceType $interfaceTypeMeta)
    {
        /** @var \GraphQL\Language\AST\NodeList $directives */
        $directives = $interfaceTypeMeta->astNode->directives;
        foreach ($directives as $directive) {
            if ($directive->name->value == 'typeResolver') {
                foreach ($directive->arguments as $directiveArgument) {
                    if ($directiveArgument->name->value == 'class') {
                        return $directiveArgument->value->value;
                    }
                }
            }
        }
        return null;
    }
}
