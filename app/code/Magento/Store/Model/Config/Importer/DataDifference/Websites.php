<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\DataDifference;

use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceInterface;

class Websites implements DataDifferenceInterface
{
    /**
     * @var RuntimeConfigSource
     */
    private $runtimeConfigSource;

    /**
     * @param RuntimeConfigSource $runtimeConfigSource
     */
    public function __construct(
        RuntimeConfigSource $runtimeConfigSource
    ) {
        $this->runtimeConfigSource = $runtimeConfigSource;
    }

    public function getItemsToDelete(array $newData)
    {
        return array_diff_key(
            $this->runtimeConfigSource->get('websites'),
            $newData
        );
    }

    public function getItemsToCreate(array $newData)
    {
        return array_diff_key(
            $newData,
            $this->runtimeConfigSource->get('websites')
        );
    }

    public function getItemsToUpdate(array $newData)
    {
        $websitesToUpdate = [];

        foreach ($this->runtimeConfigSource->get('websites') as $websiteCode => $websiteData) {
            if (
                isset($newData[$websiteCode])
                && !empty(array_diff($websiteData, $newData[$websiteCode]))
            ) {
                $websitesToUpdate[$websiteCode] = $websiteData;
            }
        }

        return $websitesToUpdate;
    }
}
