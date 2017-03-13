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
class Groups implements DataDifferenceInterface
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
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get(ScopeInterface::SCOPE_GROUPS)
        );

        return array_diff_key($runtimeGroupsData, $newData);
    }

    /**
     * @inheritdoc
     */
    public function getItemsToCreate(array $newData)
    {
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get(ScopeInterface::SCOPE_GROUPS)
        );

        return array_diff_key($newData, $runtimeGroupsData);
    }

    /**
     * @inheritdoc
     */
    public function getItemsToUpdate(array $newData)
    {
        $groupsToUpdate = [];
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get(ScopeInterface::SCOPE_GROUPS)
        );

        foreach ($runtimeGroupsData as $groupCode => $groupData) {
            if (
                isset($newData[$groupCode]) && array_diff($groupData, $newData[$groupCode])
            ) {
                $groupsToUpdate[$groupCode] = array_replace($groupData, $newData[$groupCode]);
            }
        }

        return $groupsToUpdate;
    }

    /**
     * @param array $data
     * @return array
     */
    private function changeDataKeyToCode(array $data)
    {
        return array_combine(
            array_column($data, 'code'),
            array_values($data)
        );
    }
}
