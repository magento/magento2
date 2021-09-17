<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\App\ObjectManager;
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
     * @var ConfigProvider|null
     */
    private $configProvider;

    /**
     * @param int $complexArrayItemLimit
     * @param ConfigProvider|null $configProvider
     */
    public function __construct(int $complexArrayItemLimit, ?ConfigProvider $configProvider = null)
    {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()
            ->get(ConfigProvider::class);
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
            throw new InvalidArgumentException(
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
