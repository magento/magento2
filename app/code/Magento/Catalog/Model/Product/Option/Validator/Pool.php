<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

use Laminas\Validator\ValidatorInterface;

class Pool
{
    /**
     * @var ValidatorInterface
     */
    protected $validators;

    /**
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Get validator
     *
     * @param string $type
     * @return ValidatorInterface
     */
    public function get($type)
    {
        return $this->validators[$type] ?? $this->validators['default'];
    }
}
