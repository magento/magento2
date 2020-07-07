<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Model\GetAssetIdByContentStatusInterface;

/**
 * Class GetAssetIdByContentStatusComposite
 */
class GetAssetIdByContentStatusComposite implements GetAssetIdByContentStatusInterface
{
    /**
     * @var GetAssetIdByContentStatus[]
     */
    private $getAssetIdByContentStatusArray;

    /**
     * GetAssetIdByContentStatusComposite constructor.
     * @param array $getAssetIdByContentStatusArray
     */
    public function __construct($getAssetIdByContentStatusArray = [])
    {
        $this->getAssetIdByContentStatusArray = $getAssetIdByContentStatusArray;
    }

    /**
     * @param string $value
     * @return array
     */
    public function execute(string $value): array
    {
        $ids = [];
        foreach ($this->getAssetIdByContentStatusArray as $getAssetIdByContentStatus) {
            $ids = array_merge($ids, $getAssetIdByContentStatus->execute($value));
        }
        return array_unique($ids);
    }
}
