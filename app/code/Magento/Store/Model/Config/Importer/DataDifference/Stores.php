<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\DataDifference;

use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class Stores implements DataDifferenceInterface
{
    /**
     * @var RuntimeConfigSource
     */
    private $runtimeConfigSource;

    /**
     * @param RuntimeConfigSource $runtimeConfigSource
     */
    public function __construct(RuntimeConfigSource $runtimeConfigSource)
    {
        $this->runtimeConfigSource = $runtimeConfigSource;
    }

    /**
     * @inheritdoc
     */
    public function getItemsToDelete(array $newData)
    {
        return array_diff_key(
            $this->runtimeConfigSource->get(ScopeInterface::SCOPE_STORES),
            $newData
        );
    }

    /**
     * @inheritdoc
     */
    public function getItemsToCreate(array $newData)
    {
        return array_diff_key(
            $newData,
            $this->runtimeConfigSource->get(ScopeInterface::SCOPE_STORES)
        );
    }

    /**
     * @inheritdoc
     */
    public function getItemsToUpdate(array $newData)
    {
        $storesToUpdate = [];

        foreach ($this->runtimeConfigSource->get(ScopeInterface::SCOPE_STORES) as $storeCode => $storeData) {
            if (
                isset($newData[$storeCode]) && array_diff($storeData, $newData[$storeCode])
            ) {
                $storesToUpdate[$storeCode] = array_replace($storeData, $newData[$storeCode]);
            }
        }

        return $storesToUpdate;
    }
}
