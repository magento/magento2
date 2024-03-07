<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Interface for processing service inputs
 */
interface ServiceInputValidatorInterface
{
    /**
     * Validate that the provided data is valid for the class
     *
     * @param string $className
     * @param array $items
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateComplexArrayType(string $className, array $items): void;

    /**
     * Filter an entity property value
     *
     * @param object $entity
     * @param string $propertyName
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void;
}
