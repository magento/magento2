<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Uri\UriResolver;

/**
 * The UndefinedConstraint Constraints
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class UndefinedConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        if (is_null($schema)) {
            return;
        }

        if (!is_object($schema)) {
            throw new InvalidArgumentException(
                'Given schema must be an object in ' . $path
                . ' but is a ' . gettype($schema)
            );
        }

        $i = is_null($i) ? "" : $i;
        $path = $this->incrementPath($path, $i);

        // check special properties
        $this->validateCommonProperties($value, $schema, $path);

        // check allOf, anyOf, and oneOf properties
        $this->validateOfProperties($value, $schema, $path);

        // check known types
        $this->validateTypes($value, $schema, $path, $i);
    }

    /**
     * Validates the value against the types
     *
     * @param mixed  $value
     * @param mixed  $schema
     * @param string $path
     * @param string $i
     */
    public function validateTypes($value, $schema = null, $path = null, $i = null)
    {
        // check array
        if (is_array($value)) {
            $this->checkArray($value, $schema, $path, $i);
        }

        // check object
        if (is_object($value) && (isset($schema->properties) || isset($schema->patternProperties) || isset($schema->additionalProperties))) {
            $this->checkObject(
                $value,
                isset($schema->properties) ? $schema->properties : null,
                $path,
                isset($schema->additionalProperties) ? $schema->additionalProperties : null,
                isset($schema->patternProperties) ? $schema->patternProperties : null
            );
        }

        // check string
        if (is_string($value)) {
            $this->checkString($value, $schema, $path, $i);
        }

        // check numeric
        if (is_numeric($value)) {
            $this->checkNumber($value, $schema, $path, $i);
        }

        // check enum
        if (isset($schema->enum)) {
            $this->checkEnum($value, $schema, $path, $i);
        }
    }

    /**
     * Validates common properties
     *
     * @param mixed  $value
     * @param mixed  $schema
     * @param string $path
     * @param string $i
     */
    protected function validateCommonProperties($value, $schema = null, $path = null, $i = "")
    {
        // if it extends another schema, it must pass that schema as well
        if (isset($schema->extends)) {
            if (is_string($schema->extends)) {
                $schema->extends = $this->validateUri($schema, $schema->extends);
            }
            if (is_array($schema->extends)) {
                foreach ($schema->extends as $extends) {
                    $this->checkUndefined($value, $extends, $path, $i);
                }
            } else {
                $this->checkUndefined($value, $schema->extends, $path, $i);
            }
        }

        // Verify required values
        if (is_object($value)) {
            if (!($value instanceof UndefinedConstraint) && isset($schema->required) && is_array($schema->required) ) {
                // Draft 4 - Required is an array of strings - e.g. "required": ["foo", ...]
                foreach ($schema->required as $required) {
                    if (!property_exists($value, $required)) {
                        $this->addError((!$path) ? $required : "$path.$required", "The property " . $required . " is required", 'required');
                    }
                }
            } else if (isset($schema->required) && !is_array($schema->required)) {
                // Draft 3 - Required attribute - e.g. "foo": {"type": "string", "required": true}
                if ( $schema->required && $value instanceof UndefinedConstraint) {
                    $this->addError($path, "Is missing and it is required", 'required');
                }
            }
        }

        // Verify type
        if (!($value instanceof UndefinedConstraint)) {
            $this->checkType($value, $schema, $path);
        }

        // Verify disallowed items
        if (isset($schema->disallow)) {
            $initErrors = $this->getErrors();

            $typeSchema = new \stdClass();
            $typeSchema->type = $schema->disallow;
            $this->checkType($value, $typeSchema, $path);

            // if no new errors were raised it must be a disallowed value
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, "Disallowed value was matched", 'disallow');
            } else {
                $this->errors = $initErrors;
            }
        }

        if (isset($schema->not)) {
            $initErrors = $this->getErrors();
            $this->checkUndefined($value, $schema->not, $path, $i);

            // if no new errors were raised then the instance validated against the "not" schema
            if (count($this->getErrors()) == count($initErrors)) {
                $this->addError($path, "Matched a schema which it should not", 'not');
            } else {
                $this->errors = $initErrors;
            }
        }

        // Verify minimum and maximum number of properties
        if (is_object($value)) {
            if (isset($schema->minProperties)) {
                if (count(get_object_vars($value)) < $schema->minProperties) {
                    $this->addError($path, "Must contain a minimum of " . $schema->minProperties . " properties", 'minProperties', array('minProperties' => $schema->minProperties,));
                }
            }
            if (isset($schema->maxProperties)) {
                if (count(get_object_vars($value)) > $schema->maxProperties) {
                    $this->addError($path, "Must contain no more than " . $schema->maxProperties . " properties", 'maxProperties', array('maxProperties' => $schema->maxProperties,));
                }
            }
        }

        // Verify that dependencies are met
        if (is_object($value) && isset($schema->dependencies)) {
            $this->validateDependencies($value, $schema->dependencies, $path);
        }
    }

    /**
     * Validate allOf, anyOf, and oneOf properties
     *
     * @param mixed  $value
     * @param mixed  $schema
     * @param string $path
     * @param string $i
     */
    protected function validateOfProperties($value, $schema, $path, $i = "")
    {
        // Verify type
        if ($value instanceof UndefinedConstraint) {
            return;
        }

        if (isset($schema->allOf)) {
            $isValid = true;
            foreach ($schema->allOf as $allOf) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($value, $allOf, $path, $i);
                $isValid = $isValid && (count($this->getErrors()) == count($initErrors));
            }
            if (!$isValid) {
                $this->addError($path, "Failed to match all schemas", 'allOf');
            }
        }

        if (isset($schema->anyOf)) {
            $isValid = false;
            $startErrors = $this->getErrors();
            foreach ($schema->anyOf as $anyOf) {
                $initErrors = $this->getErrors();
                $this->checkUndefined($value, $anyOf, $path, $i);
                if ($isValid = (count($this->getErrors()) == count($initErrors))) {
                    break;
                }
            }
            if (!$isValid) {
                $this->addError($path, "Failed to match at least one schema", 'anyOf');
            } else {
                $this->errors = $startErrors;
            }
        }

        if (isset($schema->oneOf)) {
            $allErrors = array();
            $matchedSchemas = 0;
            $startErrors = $this->getErrors();
            foreach ($schema->oneOf as $oneOf) {
                $this->errors = array();
                $this->checkUndefined($value, $oneOf, $path, $i);
                if (count($this->getErrors()) == 0) {
                    $matchedSchemas++;
                }
                $allErrors = array_merge($allErrors, array_values($this->getErrors()));
            }
            if ($matchedSchemas !== 1) {
                $this->addErrors(
                    array_merge(
                        $allErrors,
                        array(array(
                            'property' => $path,
                            'message' => "Failed to match exactly one schema",
                            'constraint' => 'oneOf',
                        ),),
                        $startErrors
                    )
                );
            } else {
                $this->errors = $startErrors;
            }
        }
    }

    /**
     * Validate dependencies
     *
     * @param mixed  $value
     * @param mixed  $dependencies
     * @param string $path
     * @param string $i
     */
    protected function validateDependencies($value, $dependencies, $path, $i = "")
    {
        foreach ($dependencies as $key => $dependency) {
            if (property_exists($value, $key)) {
                if (is_string($dependency)) {
                    // Draft 3 string is allowed - e.g. "dependencies": {"bar": "foo"}
                    if (!property_exists($value, $dependency)) {
                        $this->addError($path, "$key depends on $dependency and $dependency is missing", 'dependencies');
                    }
                } else if (is_array($dependency)) {
                    // Draft 4 must be an array - e.g. "dependencies": {"bar": ["foo"]}
                    foreach ($dependency as $d) {
                        if (!property_exists($value, $d)) {
                            $this->addError($path, "$key depends on $d and $d is missing", 'dependencies');
                        }
                    }
                } else if (is_object($dependency)) {
                    // Schema - e.g. "dependencies": {"bar": {"properties": {"foo": {...}}}}
                    $this->checkUndefined($value, $dependency, $path, $i);
                }
            }
        }
    }

    protected function validateUri($schema, $schemaUri = null)
    {
        $resolver = new UriResolver();
        $retriever = $this->getUriRetriever();

        $jsonSchema = null;
        if ($resolver->isValid($schemaUri)) {
            $schemaId = property_exists($schema, 'id') ? $schema->id : null;
            $jsonSchema = $retriever->retrieve($schemaId, $schemaUri);
        }

        return $jsonSchema;
    }
}
