<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentApi\Model\Composite;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\MediaContentApi\Api\GetAssetIdsByContentFieldInterface as GetAssetIdsByContentFieldApiInterface;
use Magento\MediaContentApi\Model\GetAssetIdsByContentFieldInterface;

/**
 * Class responsible to return Asset ids by content field
 */
class GetAssetIdsByContentField implements GetAssetIdsByContentFieldApiInterface
{
    /**
     * @var array
     */
    private $fieldHandlers;

    /**
     * GetAssetIdsByContentField constructor.
     *
     * @param array $fieldHandlers
     */
    public function __construct(array $fieldHandlers = [])
    {
        $this->fieldHandlers = $fieldHandlers;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $field, string $value): array
    {
        if (!array_key_exists($field, $this->fieldHandlers)) {
            throw new InvalidArgumentException(__('The field argument is invalid.'));
        }
        $ids = [];
        /** @var GetAssetIdsByContentFieldInterface $fieldHandler */
        foreach ($this->fieldHandlers[$field] as $fieldHandler) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $ids = array_merge($ids, $fieldHandler->execute($value));
        }
        return array_unique($ids);
    }
}
