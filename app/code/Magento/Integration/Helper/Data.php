<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Helper;

use Magento\Integration\Model\Integration as IntegrationModel;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Make ACL resource array compatible with jQuery jsTree component.
     *
     * @param array $resources
     * @param array $selectedResources
     * @return array
     */
    public function mapResources(array $resources, array $selectedResources = [])
    {
        $output = [];
        foreach ($resources as $resource) {
            $item = [];
            $item['id'] = $resource['id'];
            $item['li_attr']['data-id'] = $resource['id'];
            $item['text'] = __($resource['title']);
            $item['children'] = [];
            $item['state']['selected'] = in_array($item['id'], $selectedResources) ?? false;
            if (isset($resource['children'])) {
                $item['state']['opened'] = true;
                $item['children'] = $this->mapResources($resource['children'], $selectedResources);
            }
            $output[] = $item;
        }
        return $output;
    }

    /**
     * Check if integration is created using config file
     *
     * @param array $integrationData
     * @return bool true if integration is created using Config file
     */
    public function isConfigType($integrationData)
    {
        return isset(
            $integrationData[IntegrationModel::SETUP_TYPE]
        ) && $integrationData[IntegrationModel::SETUP_TYPE] == IntegrationModel::TYPE_CONFIG;
    }
}
