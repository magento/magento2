<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface as TypeReaderComposite;
use Magento\Framework\Config\ReaderInterface;

/**
 * Reads *.graphqls files from modules and combines the results as array to be used with a library to configure objects
 */
class GraphQlReader implements ReaderInterface
{
    const GRAPHQL_PLACEHOLDER_FIELD_NAME = 'placeholder_graphql_field';

    const GRAPHQL_SCHEMA_FILE = 'schema.graphqls';

    /**
     * File locator
     *
     * @var FileResolverInterface
     */
    private $fileResolver;

    /**
     * @var TypeReaderComposite
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
     * @param FileResolverInterface $fileResolver
     * @param TypeReaderComposite $typeReader
     * @param string $fileName
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        TypeReaderComposite $typeReader,
        $fileName = self::GRAPHQL_SCHEMA_FILE,
        $defaultScope = 'global'
    ) {
        $this->fileResolver = $fileResolver;
        $this->typeReader = $typeReader;
        $this->defaultScope = $defaultScope;
        $this->fileName = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function read($scope = null) : array
    {
        $results = [];
        $scope = $scope ?: $this->defaultScope;
        $schemaFiles = $this->fileResolver->get($this->fileName, $scope);
        if (!count($schemaFiles)) {
            return $results;
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

            $partialResults = $this->readPartialTypes($schemaContent);

            $results = array_replace_recursive($results, $partialResults);
        }

        $results = $this->copyInterfaceFieldsToConcreteTypes($results);
        return $results;
    }

    /**
     * Extract types as string from schema as string
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function readPartialTypes(string $graphQlSchemaContent) : array
    {
        $partialResults = [];

        $graphQlSchemaContent = $this->addPlaceHolderInSchema($graphQlSchemaContent);

        $schema = \GraphQL\Utils\BuildSchema::build($graphQlSchemaContent);

        foreach ($schema->getTypeMap() as $typeName => $typeMeta) {
            // Only process custom types and skip built-in object types
            if ((strpos($typeName, '__') !== 0 && (!$typeMeta instanceof \GraphQL\Type\Definition\ScalarType))) {
                $type = $this->typeReader->read($typeMeta);
                if (!empty($type)) {
                    $partialResults[$typeName] = $type;
                } else {
                    throw new \LogicException("'{$typeName}' cannot be processed.");
                }
            }
        }

        $partialResults = $this->removePlaceholderFromResults($partialResults);

        return $partialResults;
    }

    /**
     * Extract types as string from a larger string that represents the graphql schema using regular expressions
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function parseTypes(string $graphQlSchemaContent) : array
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

        $parsedTypes = [];

        if (!empty($matches)) {
            foreach ($matches[0] as $matchKey => $matchValue) {
                $matches[0][$matchKey] = $this->convertInterfacesToAnnotations($matchValue);
            }

            /**
             * $matches[0] is an indexed array with the whole type definitions
             * $matches[2] is an indexed array with type names
             */
            $parsedTypes = array_combine($matches[2], $matches[0]);
        }
        return $parsedTypes;
    }

    /**
     * Copy interface fields to concrete types
     *
     * @param array $source
     * @return array
     */
    private function copyInterfaceFieldsToConcreteTypes(array $source): array
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

    /**
     * Find the implements statement and convert them to annotation to enable copy fields feature
     *
     * @param string $graphQlSchemaContent
     * @return string
     */
    private function convertInterfacesToAnnotations(string $graphQlSchemaContent): string
    {
        $implementsKindsPattern = 'implements';
        $typeNamePattern = '([_A-Za-z][_0-9A-Za-z]+)';
        $spacePattern = '([\s\t\n\r]+)';
        $spacePatternNotMandatory = '[\s\t\n\r]*';
        preg_match_all(
            "/{$spacePattern}{$implementsKindsPattern}{$spacePattern}{$typeNamePattern}"
            . "(,{$spacePatternNotMandatory}$typeNamePattern)*/im",
            $graphQlSchemaContent,
            $allMatchesForImplements
        );

        if (!empty($allMatchesForImplements)) {
            foreach (array_unique($allMatchesForImplements[0]) as $implementsString) {
                $implementsStatementString = preg_replace(
                    "/{$spacePattern}{$implementsKindsPattern}{$spacePattern}/m",
                    '',
                    $implementsString
                );
                preg_match_all(
                    "/{$typeNamePattern}+/im",
                    $implementsStatementString,
                    $implementationsMatches
                );

                if (!empty($implementationsMatches)) {
                    $annotationString = ' @implements(interfaces: [';
                    foreach ($implementationsMatches[0] as $interfaceName) {
                        $annotationString.= "\"{$interfaceName}\", ";
                    }
                    $annotationString = rtrim($annotationString, ', ');
                    $annotationString .= ']) ';
                    $graphQlSchemaContent = str_replace($implementsString, $annotationString, $graphQlSchemaContent);
                }
            }
        }

        return $graphQlSchemaContent;
    }

    /**
     * Add a placeholder field into the schema to allow parser to not throw error on empty types
     * This method is paired with @see self::removePlaceholderFromResults()
     * This is needed so that the placeholder doens't end up in the actual schema
     *
     * @param string $graphQlSchemaContent
     * @return string
     */
    private function addPlaceHolderInSchema(string $graphQlSchemaContent) :string
    {
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
        return $graphQlSchemaContent;
    }

    /**
     * Remove parsed placeholders as these should not be present in final result
     *
     * @param array $partialResults
     * @return array
     */
    private function removePlaceholderFromResults(array $partialResults) : array
    {
        $placeholderField = self::GRAPHQL_PLACEHOLDER_FIELD_NAME;
        //remove parsed placeholders
        foreach ($partialResults as $typeKeyName => $partialResultTypeArray) {
            if (isset($partialResultTypeArray['fields'][$placeholderField])) {
                //unset placeholder for fields
                unset($partialResults[$typeKeyName]['fields'][$placeholderField]);
            } elseif (isset($partialResultTypeArray['items'][$placeholderField])) {
                //unset placeholder for enums
                unset($partialResults[$typeKeyName]['items'][$placeholderField]);
            }
        }
        return $partialResults;
    }
}
