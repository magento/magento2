<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer\DataDifference;

use Magento\Store\App\Config\Source\RuntimeConfigSource;
use Magento\Store\Model\Config\Importer\DataDifferenceInterface;

class Groups implements DataDifferenceInterface
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

    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToDelete(array $newData)
    {
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode($this->runtimeConfigSource->get('groups'));

        return array_diff_key($runtimeGroupsData, $newData);
    }

    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToCreate(array $newData)
    {
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode($this->runtimeConfigSource->get('groups'));

        return array_diff_key($newData, $runtimeGroupsData);
    }

    /**
     * @param array $newData
     * @return array
     */
    public function getItemsToUpdate(array $newData)
    {
        $groupsToUpdate = [];
        $newData = $this->changeDataKeyToCode($newData);
        $runtimeGroupsData = $this->changeDataKeyToCode($this->runtimeConfigSource->get('groups'));

        foreach ($runtimeGroupsData as $groupCode => $groupData) {
            if (
                isset($newData[$groupCode])
                && !empty(array_diff($groupData, $newData[$groupCode]))
            ) {
                $groupsToUpdate[$groupCode] = $groupData;
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
