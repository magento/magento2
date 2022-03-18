<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Validator\IOLimit\IOLimitConfigProvider;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;

/**
 * Enforces limits on SearchCriteria arguments
 */
class SearchCriteriaValidator implements ValidatorInterface
{
    /**
     * @var int
     */
    private $maxPageSize;

    /**
     * @var IOLimitConfigProvider|null
     */
    private $configProvider;

    /**
     * @param int $maxPageSize
     * @param IOLimitConfigProvider|null $configProvider
     */
    public function __construct(int $maxPageSize, ?IOLimitConfigProvider $configProvider = null)
    {
        $this->maxPageSize = $maxPageSize;
        $this->configProvider = $configProvider ?? ObjectManager::getInstance()
                ->get(IOLimitConfigProvider::class);
    }

    /**
     * @inheritDoc
     */
    public function validate(Field $field, $args): void
    {
        if (!$this->configProvider->isInputLimitingEnabled()) {
            return;
        }

        $max = $this->configProvider->getMaximumPageSize() ?? $this->maxPageSize;

        if (isset($args['pageSize']) && $args['pageSize'] > $max) {
            throw new GraphQlInputException(
                __("Maximum pageSize is %max", ['max' => $max])
            );
        }
    }
}
