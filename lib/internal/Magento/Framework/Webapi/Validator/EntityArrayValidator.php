<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Validates service input
 */
class EntityArrayValidator implements ServiceInputValidatorInterface
{
    /**
     * @var int
     */
    private $complexArrayItemLimit;

    /**
     * @param int $complexArrayItemLimit
     */
    public function __construct(int $complexArrayItemLimit)
    {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
    }

    /**
     * @inheritDoc
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        if (count($items) > $this->complexArrayItemLimit) {
            throw new InvalidArgumentException(
                __(
                    'Maximum items of type "%type" is %max',
                    ['type' => $className, 'max' => $this->complexArrayItemLimit]
                )
            );
        }
    }

    /**
     * @inheritDoc
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
    }
}
