<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQlSchemaStitching;

use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\BuildSchema;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Type\TypeManagement;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\Reader\InterfaceType;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader\TypeMetaReaderInterface as TypeReaderComposite;

/**
 * Reads *.graphqls files from modules and combines the results as array to be used with a library to configure objects
 */
class GraphQlReader implements ReaderInterface
{
    public const GRAPHQL_PLACEHOLDER_FIELD_NAME = 'placeholder_graphql_field';

    public const GRAPHQL_SCHEMA_FILE = 'schema.graphqls';

    /** @deprecated */
    public const GRAPHQL_INTERFACE = 'graphql_interface';

    /**
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
        $typeManagement = new TypeManagement();
        $typeManagement->overrideStandardGraphQLTypes();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null): array
    {
        $results = [];
        $scope = $scope ?: $this->defaultScope;
        $schemaFiles = $this->fileResolver->get($this->fileName, $scope);
        if (!count($schemaFiles)) {
            return $results;
        }

        /**
         * Gather as many schema together to be parsed in one go for performance
         * Collect any duplicate types in an array to retry after the initial large parse
         *
         * Compatible with @see GraphQlReader::parseTypes
         */
        $typesToRedo = [];
        $knownTypes = [];
        foreach ($schemaFiles as $partialSchemaContent) {
            $partialSchemaTypes = $this->parseTypesWithUnionHandling($partialSchemaContent);

            // Filter out duplicated ones and save them into a list to be retried
            $tmpTypes = $knownTypes;
            foreach ($partialSchemaTypes as $intendedKey => $partialSchemaType) {
                if (isset($tmpTypes[$intendedKey])) {
                    if (!isset($typesToRedo[$intendedKey])) {
                        $typesToRedo[$intendedKey] = [];
                    }
                    $typesToRedo[$intendedKey][] = $partialSchemaType;
                    continue;
                }
                $tmpTypes[$intendedKey] = $partialSchemaType;
            }
            $knownTypes = $tmpTypes;
        }

        /**
         * Read this large batch of data, this builds most of the $results array
         */
        $schemaContent = implode("\n", $knownTypes);
        $results = $this->readPartialTypes($schemaContent);

        /**
         * Go over the list of types to be retried and batch them up into as few batches as possible
         */
        $typesToRedoBatches = [];
        foreach ($typesToRedo as $type => $batches) {
            foreach ($batches as $id => $data) {
                if (!isset($typesToRedoBatches[$id])) {
                    $typesToRedoBatches[$id] = [];
                }
                $typesToRedoBatches[$id][$type] = $data;
            }
        }

        /**
         * Process each remaining batch with the minimal amount of additional schema data for performance
         */
        foreach ($typesToRedoBatches as $typesToRedoBatch) {
            $typesToUse =  $this->getTypesToUse($typesToRedoBatch, $knownTypes);
            $knownTypes = $typesToUse + $knownTypes;
            $schemaContent = implode("\n", $typesToUse);

            $partialResults = $this->readPartialTypes($schemaContent);
            $results = array_replace_recursive($results, $partialResults);
        }

        $results = $this->copyInterfaceFieldsToConcreteTypes($results);
        return $results;
    }

    /**
     * Get the minimum amount of additional types so that performance is improved
     *
     * The use of a strpos check here is a bit odd in the context of feeding data into an AST but for the performance
     * gains and to prevent downtime it is necessary
     *
     * @link https://github.com/webonyx/graphql-php/issues/244
     * @link https://github.com/webonyx/graphql-php/issues/244#issuecomment-383912418
     *
     * @param array $typesToRedoBatch
     * @param array $types
     * @return array
     */
    private function getTypesToUse($typesToRedoBatch, $types): array
    {
        $totalKnownSymbolsCount = count($typesToRedoBatch) + count($types);

        $typesToUse = $typesToRedoBatch;
        for ($i=0; $i < $totalKnownSymbolsCount; $i++) {
            $changesMade = false;
            $schemaContent = implode("\n", $typesToUse);
            foreach ($types as $type => $schema) {
                if ((!isset($typesToUse[$type]) && strpos($schemaContent, $type) !== false)) {
                    $typesToUse[$type] = $schema;
                    $changesMade = true;
                }
            }
            if (!$changesMade) {
                break;
            }
        }
        return $typesToUse;
    }

    /**
     * Extract types as string from schema as string
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function readPartialTypes(string $graphQlSchemaContent): array
    {
        $partialResults = [];

        $graphQlSchemaContent = $this->addPlaceHolderInSchema($graphQlSchemaContent);

        $schema = BuildSchema::build($graphQlSchemaContent, null, ['assumeValid'=> true, 'assumeValidSDL' => true]);

        foreach ($schema->getTypeMap() as $typeName => $typeMeta) {
            // Only process custom types and skip built-in object types
            if ((strpos($typeName, '__') !== 0 && (!$typeMeta instanceof ScalarType))) {
                $type = $this->typeReader->read($typeMeta);
                if (!empty($type)) {
                    $partialResults[$typeName] = $type;
                } else {
                    throw new \LogicException("'{$typeName}' cannot be processed.");
                }
            }
        }

        return $this->removePlaceholderFromResults($partialResults);
    }

    /**
     * Extract types as string from a larger string that represents the graphql schema using regular expressions
     *
     * The regex in parseTypes does not have the ability to split out the union data from the type below it for example
     *
     *  > union X = Y | Z
     *  >
     *  > type foo {}
     *
     * This would produce only type key from parseTypes, X, which would contain also the type foo entry.
     *
     * This wrapper does some post processing as a workaround to split out the union data from the type data below it
     * which would give us two entries, X and foo
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function parseTypesWithUnionHandling(string $graphQlSchemaContent): array
    {
        $types = $this->parseTypes($graphQlSchemaContent);

        /*
         * A union schema contains also the data from the schema below it
         *
         * If there are two newlines in this union schema then it has data below its definition, meaning it contains
         * type information not relevant to its actual type
         */
        $unionTypes = array_filter(
            $types,
            function ($t) {
                return (strpos((string)$t, 'union ') !== false) && (strpos((string)$t, PHP_EOL . PHP_EOL) !== false);
            }
        );

        foreach ($unionTypes as $type => $schema) {
            $splitSchema = explode(PHP_EOL . PHP_EOL, (string)$schema);
            // Get the type data at the bottom, this will be the additional type data not related to the union
            $additionalTypeSchema = end($splitSchema);
            // Parse the additional type from the bottom so we can have its type key => schema pair
            $additionalTypeData = $this->parseTypes($additionalTypeSchema);
            // Fix the union type schema so it does not contain the definition below it
            $types[$type] = str_replace($additionalTypeSchema, '', $schema);
            // Append the additional data to types array
            $additionalTypeKey = array_key_first($additionalTypeData);
            $types[$additionalTypeKey] = $additionalTypeData[$additionalTypeKey];
        }

        return $types;
    }

    /**
     * Extract types as string from a larger string that represents the graphql schema using regular expressions
     *
     * @param string $graphQlSchemaContent
     * @return string[] [$typeName => $typeDeclaration, ...]
     */
    private function parseTypes(string $graphQlSchemaContent): array
    {
        $typeKindsPattern = '(type|interface|union|enum|input)';
        $typeNamePattern = '([_A-Za-z][_0-9A-Za-z]+)';
        $typeDefinitionPattern = '([^\{\}]*)(\{[^\}]*\})';
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
            if ($interface['type'] ?? '' == InterfaceType::GRAPHQL_INTERFACE) {
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
            . "(,{$spacePatternNotMandatory}|({$spacePatternNotMandatory}&{$spacePatternNotMandatory})?"
            . "$typeNamePattern)*/im",
            $graphQlSchemaContent,
            $allMatchesForImplements
        );

        if (!empty($allMatchesForImplements)) {
            foreach (array_unique($allMatchesForImplements[0]) as $implementsString) {
                $implementsString = $implementsString ?? '';
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
    private function addPlaceHolderInSchema(string $graphQlSchemaContent): string
    {
        $placeholderField = self::GRAPHQL_PLACEHOLDER_FIELD_NAME;
        $typesKindsPattern = '(type|interface|input|union)';
        $enumKindsPattern = '(enum)';
        $typeNamePattern = '([_A-Za-z][_0-9A-Za-z]+)';
        $typeDefinitionPattern = '([^\{\}]*)(\{[\s\t\n\r^\}]*\})';
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
    private function removePlaceholderFromResults(array $partialResults): array
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
