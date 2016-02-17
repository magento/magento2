<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

/**
 * The ObjectConstraint Constraints, validates an object against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class ObjectConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    function check($element, $definition = null, $path = null, $additionalProp = null, $patternProperties = null)
    {
        if ($element instanceof UndefinedConstraint) {
            return;
        }

        $matches = array();
        if ($patternProperties) {
            $matches = $this->validatePatternProperties($element, $path, $patternProperties);
        }

        if ($definition) {
            // validate the definition properties
            $this->validateDefinition($element, $definition, $path);
        }

        // additional the element properties
        $this->validateElement($element, $matches, $definition, $path, $additionalProp);
    }

    public function validatePatternProperties($element, $path, $patternProperties)
    {
        $try = array('/','#','+','~','%');
        $matches = array();
        foreach ($patternProperties as $pregex => $schema) {
            $delimiter = '/';
            // Choose delimiter. Necessary for patterns like ^/ , otherwise you get error
            foreach ($try as $delimiter) {
                if (strpos($pregex, $delimiter) === false) { // safe to use
                    break;
                }
            }

            // Validate the pattern before using it to test for matches
            if (@preg_match($delimiter. $pregex . $delimiter, '') === false) {
                $this->addError($path, 'The pattern "' . $pregex . '" is invalid', 'pregex', array('pregex' => $pregex,));
                continue;
            }
            foreach ($element as $i => $value) {
                if (preg_match($delimiter . $pregex . $delimiter, $i)) {
                    $matches[] = $i;
                    $this->checkUndefined($value, $schema ? : new \stdClass(), $path, $i);
                }
            }
        }
        return $matches;
    }

    /**
     * Validates the element properties
     *
     * @param \stdClass $element          Element to validate
     * @param array     $matches          Matches from patternProperties (if any)
     * @param \stdClass $objectDefinition ObjectConstraint definition
     * @param string    $path             Path to test?
     * @param mixed     $additionalProp   Additional properties
     */
    public function validateElement($element, $matches, $objectDefinition = null, $path = null, $additionalProp = null)
    {
        foreach ($element as $i => $value) {

            $property = $this->getProperty($element, $i, new UndefinedConstraint());
            $definition = $this->getProperty($objectDefinition, $i);

            // no additional properties allowed
            if (!in_array($i, $matches) && $additionalProp === false && $this->inlineSchemaProperty !== $i && !$definition) {
                $this->addError($path, "The property " . $i . " is not defined and the definition does not allow additional properties", 'additionalProp');
            }

            // additional properties defined
            if (!in_array($i, $matches) && $additionalProp && !$definition) {
                if ($additionalProp === true) {
                    $this->checkUndefined($value, null, $path, $i);
                } else {
                    $this->checkUndefined($value, $additionalProp, $path, $i);
                }
            }

            // property requires presence of another
            $require = $this->getProperty($definition, 'requires');
            if ($require && !$this->getProperty($element, $require)) {
                $this->addError($path, "The presence of the property " . $i . " requires that " . $require . " also be present", 'requires');
            }

            if (!$definition) {
                // normal property verification
                $this->checkUndefined($value, new \stdClass(), $path, $i);
            }
        }
    }

    /**
     * Validates the definition properties
     *
     * @param \stdClass $element          Element to validate
     * @param \stdClass $objectDefinition ObjectConstraint definition
     * @param string    $path             Path?
     */
    public function validateDefinition($element, $objectDefinition = null, $path = null)
    {
        foreach ($objectDefinition as $i => $value) {
            $property = $this->getProperty($element, $i, new UndefinedConstraint());
            $definition = $this->getProperty($objectDefinition, $i);
            $this->checkUndefined($property, $definition, $path, $i);
        }
    }

    /**
     * retrieves a property from an object or array
     *
     * @param mixed  $element  Element to validate
     * @param string $property Property to retrieve
     * @param mixed  $fallback Default value if property is not found
     *
     * @return mixed
     */
    protected function getProperty($element, $property, $fallback = null)
    {
        if (is_array($element) /*$this->checkMode == self::CHECK_MODE_TYPE_CAST*/) {
            return array_key_exists($property, $element) ? $element[$property] : $fallback;
        } elseif (is_object($element)) {
            return property_exists($element, $property) ? $element->$property : $fallback;
        }

        return $fallback;
    }
}
