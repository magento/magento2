<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Uri\Retrievers\UriRetrieverInterface;
use JsonSchema\Uri\UriRetriever;

/**
 * Take in an object that's a JSON schema and take care of all $ref references
 *
 * @author Tyler Akins <fidian@rumkin.com>
 * @see    README.md
 */
class RefResolver
{
    /**
     * HACK to prevent too many recursive expansions.
     * Happens e.g. when you want to validate a schema against the schema
     * definition.
     *
     * @var integer
     */
    protected static $depth = 0;

    /**
     * maximum references depth
     * @var integer
     */
    public static $maxDepth = 7;

    /**
     * @var UriRetrieverInterface
     */
    protected $uriRetriever = null;

    /**
     * @var object
     */
    protected $rootSchema = null;

    /**
     * @param UriRetriever $retriever
     */
    public function __construct($retriever = null)
    {
        $this->uriRetriever = $retriever;
    }

    /**
     * Retrieves a given schema given a ref and a source URI
     *
     * @param  string $ref       Reference from schema
     * @param  string $sourceUri URI where original schema was located
     * @return object            Schema
     */
    public function fetchRef($ref, $sourceUri)
    {
        $retriever  = $this->getUriRetriever();
        $jsonSchema = $retriever->retrieve($ref, $sourceUri);
        $this->resolve($jsonSchema);

        return $jsonSchema;
    }

    /**
     * Return the URI Retriever, defaulting to making a new one if one
     * was not yet set.
     *
     * @return UriRetriever
     */
    public function getUriRetriever()
    {
        if (is_null($this->uriRetriever)) {
            $this->setUriRetriever(new UriRetriever);
        }

        return $this->uriRetriever;
    }

    /**
     * Resolves all $ref references for a given schema.  Recurses through
     * the object to resolve references of any child schemas.
     *
     * The 'format' property is omitted because it isn't required for
     * validation.  Theoretically, this class could be extended to look
     * for URIs in formats: "These custom formats MAY be expressed as
     * an URI, and this URI MAY reference a schema of that format."
     *
     * The 'id' property is not filled in, but that could be made to happen.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
     */
    public function resolve($schema, $sourceUri = null)
    {
        if (self::$depth > self::$maxDepth) {
            self::$depth = 0;
            throw new JsonDecodingException(JSON_ERROR_DEPTH);
        }
        ++self::$depth;

        if (! is_object($schema)) {
            --self::$depth;
            return;
        }

        if (null === $sourceUri && ! empty($schema->id)) {
            $sourceUri = $schema->id;
        }

        if (null === $this->rootSchema) {
            $this->rootSchema = $schema;
        }

        // Resolve $ref first
        $this->resolveRef($schema, $sourceUri);

        // These properties are just schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('additionalItems', 'additionalProperties', 'extends', 'items') as $propertyName) {
            $this->resolveProperty($schema, $propertyName, $sourceUri);
        }

        // These are all potentially arrays that contain schema objects
        // eg.  type can be a value or an array of values/schemas
        // eg.  items can be a schema or an array of schemas
        foreach (array('disallow', 'extends', 'items', 'type', 'allOf', 'anyOf', 'oneOf') as $propertyName) {
            $this->resolveArrayOfSchemas($schema, $propertyName, $sourceUri);
        }

        // These are all objects containing properties whose values are schemas
        foreach (array('dependencies', 'patternProperties', 'properties') as $propertyName) {
            $this->resolveObjectOfSchemas($schema, $propertyName, $sourceUri);
        }

        --self::$depth;
    }

    /**
     * Given an object and a property name, that property should be an
     * array whose values can be schemas.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveArrayOfSchemas($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName) || ! is_array($schema->$propertyName)) {
            return;
        }

        foreach ($schema->$propertyName as $possiblySchema) {
            $this->resolve($possiblySchema, $sourceUri);
        }
    }

    /**
     * Given an object and a property name, that property should be an
     * object whose properties are schema objects.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveObjectOfSchemas($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName) || ! is_object($schema->$propertyName)) {
            return;
        }

        foreach (get_object_vars($schema->$propertyName) as $possiblySchema) {
            $this->resolve($possiblySchema, $sourceUri);
        }
    }

    /**
     * Given an object and a property name, that property should be a
     * schema object.
     *
     * @param object $schema       JSON Schema to flesh out
     * @param string $propertyName Property to work on
     * @param string $sourceUri    URI where this schema was located
     */
    public function resolveProperty($schema, $propertyName, $sourceUri)
    {
        if (! isset($schema->$propertyName)) {
            return;
        }

        $this->resolve($schema->$propertyName, $sourceUri);
    }

    /**
     * Look for the $ref property in the object.  If found, remove the
     * reference and augment this object with the contents of another
     * schema.
     *
     * @param object $schema    JSON Schema to flesh out
     * @param string $sourceUri URI where this schema was located
     */
    public function resolveRef($schema, $sourceUri)
    {
        $ref = '$ref';

        if (empty($schema->$ref)) {
            return;
        }

        $splitRef = explode('#', $schema->$ref, 2);

        $refDoc = $splitRef[0];
        $refPath = null;
        if (count($splitRef) === 2) {
            $refPath = explode('/', $splitRef[1]);
            array_shift($refPath);
        }

        if (empty($refDoc) && empty($refPath)) {
            // TODO: Not yet implemented - root pointer ref, causes recursion issues
            return;
        }

        if (!empty($refDoc)) {
            $refSchema = $this->fetchRef($refDoc, $sourceUri);
        } else {
            $refSchema = $this->rootSchema;
        }

        if (null !== $refPath) {
            $refSchema = $this->resolveRefSegment($refSchema, $refPath);
        }

        unset($schema->$ref);

        // Augment the current $schema object with properties fetched
        foreach (get_object_vars($refSchema) as $prop => $value) {
            $schema->$prop = $value;
        }
    }

    /**
     * Set URI Retriever for use with the Ref Resolver
     *
     * @param UriRetriever $retriever
     * @return $this for chaining
     */
    public function setUriRetriever(UriRetriever $retriever)
    {
        $this->uriRetriever = $retriever;

        return $this;
    }

    protected function resolveRefSegment($data, $pathParts)
    {
        foreach ($pathParts as $path) {
            $path = strtr($path, array('~1' => '/', '~0' => '~', '%25' => '%'));

            if (is_array($data)) {
                $data = $data[$path];
            } else {
                $data = $data->{$path};
            }
        }

        return $data;
    }
}
