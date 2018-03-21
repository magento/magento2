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

class GraphQlReader implements ReaderInterface
{
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
     * @param FileResolverInterface $fileResolver
     * @param TypeReader $typeReader
     * @param string $fileName
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        TypeReader $typeReader,
        $fileName = 'schema.graphql',
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
        $typeNamePattern = '[_A-Za-z][_0-9A-Za-z]+';
        $typeDefinitionPattern = '[^\{]*(\{[^\}]*\})';
        $spacePattern = '[\s\t\n\r]+';

        preg_match_all(
            "/{$typeKindsPattern}{$spacePattern}({$typeNamePattern}){$spacePattern}{$typeDefinitionPattern}/i",
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
}
