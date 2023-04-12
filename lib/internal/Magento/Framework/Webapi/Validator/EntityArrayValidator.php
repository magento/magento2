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
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;
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
    private int $complexArrayItemLimit;

    /**
     * @var IOLimitConfigProvider
     */
    private $configProvider;

    /**
     * @var InputArraySizeLimitValue
     */
    private $inputArraySizeLimitValue;

    /**
     * @param int $complexArrayItemLimit
     * @param IOLimitConfigProvider|null $configProvider
     * @param InputArraySizeLimitValue|null $inputArraySizeLimitValue
     */
    public function __construct(
        int $complexArrayItemLimit,
        ?IOLimitConfigProvider $configProvider = null,
        ?InputArraySizeLimitValue $inputArraySizeLimitValue = null
    ) {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()->get(IOLimitConfigProvider::class);
        $this->inputArraySizeLimitValue = $inputArraySizeLimitValue ?? ObjectManager::getInstance()
                ->get(InputArraySizeLimitValue::class);
    }

    /**
     * @inheritDoc
     *
     * @throws FileSystemException|RuntimeException
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        if (!$this->configProvider->isInputLimitingEnabled()) {
            return;
        }

        $maxLimit = $this->inputArraySizeLimitValue->get()
            ?? ($this->configProvider->getComplexArrayItemLimit() ?? $this->complexArrayItemLimit);

        if (count($items) > $maxLimit) {
            throw new InvalidArgumentException(
                __(
                    'Maximum items of type "%type" is %max',
                    ['type' => $className, 'max' => $maxLimit]
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
