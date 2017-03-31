<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param string $scope The data scope
     * @param array $data The data of scopes (websites, groups, stores)
     * @return array
     */
    private function setDefaultValues($scope, array $data)
    {
        $fieldset = [];
        switch ($scope) {
            case ScopeInterface::SCOPE_WEBSITES:
                $fieldset = ['default_group_id'];
                break;
            case ScopeInterface::SCOPE_GROUPS:
                $fieldset = ['website_id', 'default_store_id', 'root_category_id'];
                break;
            case ScopeInterface::SCOPE_STORES:
                $fieldset = ['website_id', 'group_id'];
                break;
        }

        foreach ($data as $groupCode => $groupData) {
            foreach ($fieldset as $field) {
                $groupData[$field] = !empty($groupData[$field]) ? $groupData[$field] : '0';
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
        $runtimeData = $this->runtimeConfigSource->get();

        return (array)$runtimeData[$scope];
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
