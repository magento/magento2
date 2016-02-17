<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Constraints;

use JsonSchema\Exception\InvalidArgumentException;

/**
 * The SchemaConstraint Constraints, validates an element against a given schema
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class SchemaConstraint extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function check($element, $schema = null, $path = null, $i = null)
    {
        if ($schema !== null) {
            // passed schema
            $this->checkUndefined($element, $schema, '', '');
        } elseif (property_exists($element, $this->inlineSchemaProperty)) {
            // inline schema
            $this->checkUndefined($element, $element->{$this->inlineSchemaProperty}, '', '');
        } else {
            throw new InvalidArgumentException('no schema found to verify against');
        }
    }
}