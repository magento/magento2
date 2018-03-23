<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\GraphQl\Config\GraphQlReader\TypeReader;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Config\Converter\NormalizerComposite;

class GraphQlReader implements ReaderInterface
{

    const GRAPHQL_PLACEHOLDER_FIELD_NAME = 'placeholder_graphql_field';

    /**
     * File locator
     *
     * @var FileResolverInterface
     */
    private $fileResolver;

    /**
     * @var TypeReader
     */
    private $typeReader;


    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $defaultScope;

    /**
     * @var NormalizerComposite
     */
    private $normalizer;

    /**
     * @param FileResolverInterface $fileResolver
     * @param TypeReader $typeReader
     * @param NormalizerComposite $normalizer
     * @param string $fileName
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        TypeReader $typeReader,
        NormalizerComposite $normalizer,
        $fileName = 'schema.graphql',
        $defaultScope = 'global'
    ) {
        $this->fileResolver = $fileResolver;
        $this->typeReader = $typeReader;
        $this->normalizer = $normalizer;
        $this->defaultScope = $defaultScope;
        $this->fileName = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function read($scope = null) : array
    {
        $result = [];
        $scope = $scope ?: $this->defaultScope;
        $schemaFiles = $this->fileResolver->get($this->fileName, $scope);
        if (!count($schemaFiles)) {
            return $result;
        }

        /**
         * Compatible with @see GraphQlReader::parseTypes
         */
        $knownTypes = [];
        foreach ($schemaFiles as $partialSchemaContent) {
            $partialSchemaTypes = $this->parseTypes($partialSchemaContent);
            // Keep declarations from current partial schema, add missing declarations from all previously read schemas
            $knownTypes = $partialSchemaTypes + $knownTypes;
            $schemaContent = implode("\n", $knownTypes);

            $partialResult = $this->readPartialTypes($schemaContent);

            $result = array_replace_recursive($result, $partialResult);
        }

        $result = $this->copyInterfaceFieldsToConcreteTypes($result);
        return $result;
    }


    /**
     * Extract types as string from schema as string
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function readPartialTypes(string $graphQlSchemaContent) : array
    {
        $partialResult = [];
        $placeholderField = self::GRAPHQL_PLACEHOLDER_FIELD_NAME;
        $typesKindsPattern = '(type|interface|input)';
        $enumKindsPattern = '(enum)';
        $typeNamePattern = '([_A-Za-z][_0-9A-Za-z]+)';
        $typeDefinitionPattern = '([^\{]*)(\{[\s\t\n\r^\}]*\})';
        $spacePattern = '([\s\t\n\r]+)';

        //add placeholder in empty types
        $graphQlSchemaContent = preg_replace(
            "/{$typesKindsPattern}{$spacePattern}{$typeNamePattern}{$spacePattern}{$typeDefinitionPattern}/im",
            "\$1\$2\$3\$4\$5{\n{$placeholderField}: String\n}",
            $graphQlSchemaContent
        );

        //add placeholder in empty enums
        $graphQlSchemaContent = preg_replace(
            "/{$enumKindsPattern}{$spacePattern}{$typeNamePattern}{$spacePattern}{$typeDefinitionPattern}/im",
            "\$1\$2\$3\$4\$5{\n{$placeholderField}\n}",
            $graphQlSchemaContent
        );

        $schema = \GraphQL\Utils\BuildSchema::build($graphQlSchemaContent);

        foreach ($schema->getTypeMap() as $typeName => $typeMeta) {
            // Only process custom types and skip built-in object types
            if ((strpos($typeName, '__') !== 0 && (!$typeMeta instanceof \GraphQL\Type\Definition\ScalarType))) {
                $partialResult[$typeName] = $this->typeReader->read($typeMeta);
                if (!$partialResult[$typeName]) {
                    throw new \LogicException("'{$typeName}' cannot be processed.");
                }
            }
        }

        //remove parsed placeholders
        foreach ($partialResult as $typeName => $partialResultType) {
            if (isset($partialResultType['fields'][$placeholderField])) {
                //unset placeholder for fields
                unset($partialResult[$typeName]['fields'][$placeholderField]);
            } elseif (isset($partialResultType['items'][$placeholderField])) {
                //unset placeholder for enums
                unset($partialResult[$typeName]['items'][$placeholderField]);
            }
        }

        return $partialResult;
    }

    /**
     * Extract types as string from a larger string that represents the graphql schema using regular expressions
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function parseTypes($graphQlSchemaContent) : array
    {
        $typeKindsPattern = '(type|interface|union|enum|input)';
        $typeNamePattern = '([_A-Za-z][_0-9A-Za-z]+)';
        $typeDefinitionPattern = '([^\{]*)(\{[^\}]*\})';
        $spacePattern = '[\s\t\n\r]+';

        preg_match_all(
            "/{$typeKindsPattern}{$spacePattern}{$typeNamePattern}{$spacePattern}{$typeDefinitionPattern}/i",
            $graphQlSchemaContent,
            $matches
        );
        /**
         * $matches[0] is an indexed array with the whole type definitions
         * $matches[2] is an indexed array with type names
         */
        $parsedTypes = array_combine($matches[2], $matches[0]);
        return $parsedTypes;
    }

    /**
     * Copy interface fields to concrete types
     *
     * @param array $source
     * @return array
     */
    public function copyInterfaceFieldsToConcreteTypes(array $source): array
    {
        foreach ($source as $interface) {
            if ($interface['type'] == 'graphql_interface') {
                foreach ($source as $typeName => $type) {
                    if (isset($type['implements'])
                        && isset($type['implements'][$interface['name']])
                        && isset($type['implements'][$interface['name']]['copyFields'])
                        && $type['implements'][$interface['name']]['copyFields'] === true
                    ) {
                        $source[$typeName]['fields'] = isset($type['fields'])
                            ? array_replace($interface['fields'], $type['fields']) : $interface['fields'];
                    }
                }
            }
        }

        return $source;
    }
}
