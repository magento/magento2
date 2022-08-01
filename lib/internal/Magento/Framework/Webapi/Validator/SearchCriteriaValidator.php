<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Validator\IOLimit\IOLimitConfigProvider;

/**
 * Validates search criteria inputs
 */
class SearchCriteriaValidator implements ServiceInputValidatorInterface
{
    /**
     * @var int
     */
    private $maximumPageSize;

    /**
     * @var IOLimitConfigProvider|null
     */
    private $configProvider;

    /**
     * @param int $maximumPageSize
     * @param IOLimitConfigProvider|null $configProvider
     */
    public function __construct(int $maximumPageSize, ?IOLimitConfigProvider $configProvider = null)
    {
        $this->maximumPageSize = $maximumPageSize;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()
                ->get(IOLimitConfigProvider::class);
    }

    /**
     * @inheritDoc
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
    }

    /**
     * @inheritDoc
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
        if ($entity instanceof SearchCriteriaInterface
            && $propertyName === 'pageSize'
            && $this->configProvider->isInputLimitingEnabled()
            && $value > ($max = $this->configProvider->getMaximumPageSize() ?? $this->maximumPageSize)
        ) {
            throw new LocalizedException(
                __('Maximum SearchCriteria pageSize is %max', ['max' => $max])
            );
        }
    }
}
