<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Model\GetAssetIdByContentStatusInterface;

/**
 * Class responsible to return Asset ids by content status
 */
class GetAssetIdByContentStatusComposite implements GetAssetIdByContentStatusInterface
{
    /**
     * @var GetAssetIdByContentStatus[]
     */
    private $getAssetIdByContentStatusArray;

    /**
     * GetAssetIdByContentStatusComposite constructor.
     *
     * @param array $getAssetIdByContentStatusArray
     */
    public function __construct($getAssetIdByContentStatusArray = [])
    {
        $this->getAssetIdByContentStatusArray = $getAssetIdByContentStatusArray;
    }

    /**
     * Get Asset ids by Content status
     *
     * @param string $status
     * @return array
     */
    public function execute(string $status): array
    {
        $ids = [];
        foreach ($this->getAssetIdByContentStatusArray as $getAssetIdByContentStatus) {
            $ids = array_merge($ids, $getAssetIdByContentStatus->execute($status));
        }
        return array_unique($ids);
    }
}
