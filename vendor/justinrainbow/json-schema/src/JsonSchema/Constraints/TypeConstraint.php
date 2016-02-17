<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use UnexpectedValueException as StandardUnexpectedValueException;

/**
 * The TypeConstraint Constraints, validates an element against a given type
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class TypeConstraint extends Constraint
{
    /**
     * @var array|string[] type wordings for validation error messages
     */
    static $wording = array(
        'integer' => 'an integer',
        'number'  => 'a number',
        'boolean' => 'a boolean',
        'object'  => 'an object',
        'array'   => 'an array',
        'string'  => 'a string',
        'null'    => 'a null',
        'any'     => NULL, // validation of 'any' is always true so is not needed in message wording
        0         => NULL, // validation of a false-y value is always true, so not needed as well
    );

    /**
     * {@inheritDoc}
     */
    public function check($value = null, $schema = null, $path = null, $i = null)
    {
        $type = isset($schema->type) ? $schema->type : null;
        $isValid = true;

        if (is_array($type)) {
            // @TODO refactor
            $validatedOneType = false;
            $errors = array();
            foreach ($type as $tp) {
                $validator = new TypeConstraint($this->checkMode);
                $subSchema = new \stdClass();
                $subSchema->type = $tp;
                $validator->check($value, $subSchema, $path, null);
                $error = $validator->getErrors();

                if (!count($error)) {
                    $validatedOneType = true;
                    break;
                }

                $errors = $error;
            }

            if (!$validatedOneType) {
                $this->addErrors($errors);

                return;
            }
        } elseif (is_object($type)) {
            $this->checkUndefined($value, $type, $path);
        } else {
            $isValid = $this->validateType($value, $type);
        }

        if ($isValid === false) {
            if (!isset(self::$wording[$type])) {
                throw new StandardUnexpectedValueException(
                    sprintf(
                        "No wording for %s available, expected wordings are: [%s]",
                        var_export($type, true),
                        implode(', ', array_filter(self::$wording)))
                );
            }
            $this->addError($path, ucwords(gettype($value)) . " value found, but " . self::$wording[$type] . " is required", 'type');
        }
    }

    /**
     * Verifies that a given value is of a certain type
     *
     * @param mixed  $value Value to validate
     * @param string $type  TypeConstraint to check against
     *
     * @return boolean
     *
     * @throws InvalidArgumentException
     */
    protected function validateType($value, $type)
    {
        //mostly the case for inline schema
        if (!$type) {
            return true;
        }

        if ('integer' === $type) {
            return is_int($value);
        }

        if ('number' === $type) {
            return is_numeric($value) && !is_string($value);
        }

        if ('boolean' === $type) {
            return is_bool($value);
        }

        if ('object' === $type) {
            return is_object($value);
            //return ($this::CHECK_MODE_TYPE_CAST == $this->checkMode) ? is_array($value) : is_object($value);
        }

        if ('array' === $type) {
            return is_array($value);
        }

        if ('string' === $type) {
            return is_string($value);
        }
        
        if ('email' === $type) {
            return is_string($value);
        }

        if ('null' === $type) {
            return is_null($value);
        }

        if ('any' === $type) {
            return true;
        }

        throw new InvalidArgumentException((is_object($value) ? 'object' : $value) . ' is an invalid type for ' . $type);
    }
}
