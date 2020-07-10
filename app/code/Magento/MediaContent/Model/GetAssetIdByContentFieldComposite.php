<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\GetAssetIdByContentFieldInterface as GetAssetIdByContentFieldApiInterface;
use Magento\MediaContentApi\Model\GetAssetIdByContentFieldInterface;

/**
 * Class responsible to return Asset ids by content field
 */
class GetAssetIdByContentFieldComposite implements GetAssetIdByContentFieldApiInterface
{
    /**
     * @var GetAssetIdByContentFieldInterface[]
     */
    private $getAssetIdByContentFieldArray;

    /**
     * GetAssetIdByContentStatusComposite constructor.
     *
     * @param array $getAssetIdByContentFieldArray
     */
    public function __construct($getAssetIdByContentFieldArray = [])
    {
        $this->getAssetIdByContentFieldArray = $getAssetIdByContentFieldArray;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $field, string $value): array
    {
        if (!array_key_exists($field, $this->getAssetIdByContentFieldArray)) {
            throw new InvalidArgumentException(__('The field argument is invalid.'));
        }
        $ids = [];
        foreach ($this->getAssetIdByContentFieldArray[$field] as $getAssetIdByContentField) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $ids = array_merge($ids, $getAssetIdByContentField->execute($value));
        }
        return array_unique($ids);
    }
}
