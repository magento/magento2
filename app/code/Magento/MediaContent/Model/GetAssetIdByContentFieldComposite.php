<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Model\GetAssetIdByContentFieldInterface;

/**
 * Class responsible to return Asset ids by content field
 */
class GetAssetIdByContentFieldComposite implements GetAssetIdByContentFieldInterface
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
    public function execute(string $value): array
    {
        $ids = [];
        foreach ($this->getAssetIdByContentFieldArray as $getAssetIdByContentField) {
            $ids = array_merge($ids, $getAssetIdByContentField->execute($value));
        }
        return array_unique($ids);
    }
}
