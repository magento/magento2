<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Data;

use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\ValidatorInterface;

class Validator extends AbstractValidator
{
    /**
     * @param array $validators
     */
    public function __construct(
        private readonly array $validators = []
    ) {
        array_map(fn (ValidatorInterface $validator) => $validator, $validators);
    }

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $this->_addMessages($validator->getMessages());
            }
        }
        return empty($this->getMessages());
    }
}
