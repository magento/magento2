<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

/**
 * Factory for centralize constraint initialization.
 */
class Factory
{
    /**
     * @var UriRetriever $uriRetriever
     */
    protected $uriRetriever;

    /**
     * @var array $constraintMap
     */
    protected $constraintMap = array(
        'array' => 'JsonSchema\Constraints\CollectionConstraint',
        'collection' => 'JsonSchema\Constraints\CollectionConstraint',
        'object' => 'JsonSchema\Constraints\ObjectConstraint',
        'type' => 'JsonSchema\Constraints\TypeConstraint',
        'undefined' => 'JsonSchema\Constraints\UndefinedConstraint',
        'string' => 'JsonSchema\Constraints\StringConstraint',
        'number' => 'JsonSchema\Constraints\NumberConstraint',
        'enum' => 'JsonSchema\Constraints\EnumConstraint',
        'format' => 'JsonSchema\Constraints\FormatConstraint',
        'schema' => 'JsonSchema\Constraints\SchemaConstraint',
        'validator' => 'JsonSchema\Validator',
    );

    /**
     * @param UriRetriever $uriRetriever
     */
    public function __construct(UriRetriever $uriRetriever = null)
    {
        if (!$uriRetriever) {
            $uriRetriever = new UriRetriever();
        }

        $this->uriRetriever = $uriRetriever;
    }

    /**
     * @return UriRetriever
     */
    public function getUriRetriever()
    {
        return $this->uriRetriever;
    }

    /**
     * @param string $name
     * @param string $class
     * @return Factory
     */
    public function setConstraintClass($name, $class)
    {
      // Ensure class exists
      if (!class_exists($class)) {
        throw new InvalidArgumentException('Unknown constraint ' . $name);
      }
      // Ensure class is appropriate
      if (!in_array('JsonSchema\Constraints\ConstraintInterface', class_implements($class))) {
        throw new InvalidArgumentException('Invalid class ' . $name);
      }
      $this->constraintMap[$name] = $class;
      return $this;
    }

    /**
     * Create a constraint instance for the given constraint name.
     *
     * @param string $constraintName
     * @return ConstraintInterface|ObjectConstraint
     * @throws InvalidArgumentException if is not possible create the constraint instance.
     */
    public function createInstanceFor($constraintName)
    {
        if (array_key_exists($constraintName, $this->constraintMap)) {
          return new $this->constraintMap[$constraintName](Constraint::CHECK_MODE_NORMAL, $this->uriRetriever, $this);
        }
        throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
    }
}
