<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

use Magento\Framework\App\Config\ConfigSourceInterface;

/**
 * Calculates difference between current configuration and new one.
 */
class DataDifferenceCalculator
{
    /**
     * The config source to retrieve current config.
     *
     * @var ConfigSourceInterface
     */
    private $runtimeConfigSource;

    /**
     * @param ConfigSourceInterface $runtimeConfigSource The config source to retrieve current config
     */
    public function __construct(ConfigSourceInterface $runtimeConfigSource)
    {
        $this->runtimeConfigSource = $runtimeConfigSource;
    }

    /**
     * Calculates items to delete.
     *
     * @param string $scope The data scope
     * @param array $data The new data
     * @return array
     */
    public function getItemsToDelete($scope, array $data)
    {
        $data = $this->changeDataKeyToCode($data);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get($scope)
        );

        return array_diff_key($runtimeGroupsData, $data);
    }

    /**
     * Calculates items to create.
     *
     * @param string $scope The data scope
     * @param array $data The new data
     * @return array
     */
    public function getItemsToCreate($scope, array $data)
    {
        $data = $this->changeDataKeyToCode($data);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get($scope)
        );

        return array_diff_key($data, $runtimeGroupsData);
    }

    /**
     * Calculates items to update.
     *
     * @param string $scope The data scope
     * @param array $data The new data
     * @return array
     */
    public function getItemsToUpdate($scope, array $data)
    {
        $groupsToUpdate = [];
        $data = $this->changeDataKeyToCode($data);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->runtimeConfigSource->get($scope)
        );

        foreach ($runtimeGroupsData as $groupCode => $groupData) {
            if (
                isset($data[$groupCode]) && array_diff($groupData, $data[$groupCode])
            ) {
                $groupsToUpdate[$groupCode] = array_replace($groupData, $data[$groupCode]);
            }
        }

        return $groupsToUpdate;
    }

    /**
     * Create array of data keys.
     *
     * @param array $data The data
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
