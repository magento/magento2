<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Importer;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Store\Model\ScopeInterface;

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
     * The cached runtime config data.
     *
     * @var array
     */
    private $runtimeConfigData;

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
            $this->getRuntimeData($scope)
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
            $this->getRuntimeData($scope)
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
        $data = $this->setDefaultValues($scope, $data);
        $runtimeGroupsData = $this->changeDataKeyToCode(
            $this->getRuntimeData($scope)
        );

        foreach ($runtimeGroupsData as $groupCode => $groupData) {
            if (
                isset($data[$groupCode]) && array_diff_assoc($groupData, $data[$groupCode])
            ) {
                $groupsToUpdate[$groupCode] = array_replace($groupData, $data[$groupCode]);
            }
        }

        return $groupsToUpdate;
    }

    /**
     * Sets default values for some fields if their value is empty.
     *
     * @param $scope The data scope
     * @param array $data The data of scopes (websites, groups, stores)
     * @return array
     */
    private function setDefaultValues($scope, array $data)
    {
        foreach ($data as $groupCode => $groupData) {
            switch ($scope) {
                case ScopeInterface::SCOPE_WEBSITES:
                    $groupData['default_group_id'] = !empty($groupData['default_group_id'])
                        ? $groupData['default_group_id'] : '0';
                    break;
                case ScopeInterface::SCOPE_GROUPS:
                    $groupData['website_id'] = !empty($groupData['website_id']) ? $groupData['website_id'] : '0';
                    $groupData['default_store_id'] = !empty($groupData['default_store_id'])
                        ? $groupData['default_store_id'] : '0';
                    $groupData['root_category_id'] = !empty($groupData['root_category_id'])
                        ? $groupData['root_category_id']: '0';
                    break;
                case ScopeInterface::SCOPE_STORES:
                    $groupData['website_id'] = !empty($groupData['website_id'])
                        ? $groupData['website_id'] : '0';
                    $groupData['group_id'] = !empty($groupData['group_id'])
                        ? $groupData['group_id'] : '0';
                    break;
            }

            $data[$groupCode] = $groupData;
        }

        return $data;
    }

    /**
     * Retrieves runtime data for specific scope.
     *
     * @param string $scope The scope of config data
     * @return array
     */
    private function getRuntimeData($scope)
    {
        if (null === $this->runtimeConfigData) {
            $this->runtimeConfigData = $this->runtimeConfigSource->get();
        }

        return (array)$this->runtimeConfigData[$scope];
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
