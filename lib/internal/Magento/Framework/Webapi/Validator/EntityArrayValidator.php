<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;

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
     * @var IOLimitConfigProvider|null
     */
    private $configProvider;

    /**
     * @param int $complexArrayItemLimit
     * @param IOLimitConfigProvider|null $configProvider
     */
    public function __construct(int $complexArrayItemLimit, ?IOLimitConfigProvider $configProvider = null)
    {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()
                ->get(IOLimitConfigProvider::class);
    }

    /**
     * @inheritDoc
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        if (!$this->configProvider->isInputLimitingEnabled()) {
            return;
        }

        $max = $this->configProvider->getComplexArrayItemLimit() ?? $this->complexArrayItemLimit;

        if (count($items) > $max) {
            throw new LocalizedException(
                __(
                    'Maximum items of type "%type" is %max',
                    ['type' => $className, 'max' => $max]
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
