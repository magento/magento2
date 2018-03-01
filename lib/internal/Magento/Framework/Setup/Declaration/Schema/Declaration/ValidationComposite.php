<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration;

use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;

/**
 * This validator holds different validations rules.
 *
 * @inheritdoc
 */
class ValidationComposite implements ValidationInterface
{
    /**
     * @var ValidationInterface[]
     */
    private $rules;

    /**
     * Constructor.
     *
     * @param ValidationInterface[] $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @inheritdoc
     */
    public function validate(Schema $schema)
    {
        $errors = [];

        foreach ($this->rules as $rule) {
            $errors = array_replace_recursive(
                $errors,
                $rule->validate($schema)
            );
        }

        return $errors;
    }
}
