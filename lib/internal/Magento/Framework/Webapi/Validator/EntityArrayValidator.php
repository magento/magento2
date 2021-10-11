<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;

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
     * @var InputArraySizeLimitValue
     */
    private $inputArraySizeLimitValue;

    /**
     * @param int $complexArrayItemLimit
     * @param InputArraySizeLimitValue|null $inputArraySizeLimitValue
     */
    public function __construct(
        int $complexArrayItemLimit,
        ?InputArraySizeLimitValue $inputArraySizeLimitValue = null
    ) {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
        $this->inputArraySizeLimitValue = $inputArraySizeLimitValue ?? ObjectManager::getInstance()
                ->get(InputArraySizeLimitValue::class);
    }

    /**
     * @inheritDoc
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        $limit = $this->inputArraySizeLimitValue->get() ?? $this->complexArrayItemLimit;
        if (count($items) > $limit) {
            throw new InvalidArgumentException(
                __(
                    'Maximum items of type "%type" is %max',
                    ['type' => $className, 'max' => $limit]
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
