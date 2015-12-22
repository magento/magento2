<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return array
     */
    public function mapResources(array $resources)
    {
        $output = [];
        foreach ($resources as $resource) {
            $item = [];
            $item['attr']['data-id'] = $resource['id'];
            $item['data'] = $resource['title'];
            $item['children'] = [];
            if (isset($resource['children'])) {
                $item['state'] = 'open';
                $item['children'] = $this->mapResources($resource['children']);
            }
            $output[] = $item;
        }
        return $output;
    }

    /**
     * Make ACL resource array return a hash with parent-resource-name => [children-resources-names] relationship
     *
     * @param array $resources
     * @return array
     */
    public function hashResources(array $resources)
    {
        $output = [];
        foreach ($resources as $resource) {
            if (isset($resource['children'])) {
                $item = $this->hashResources($resource['children']);
            } else {
                $item = [];
            }
            $output[$resource['id']] = $item;
        }
        return $output;
    }

    /**
     * Find parents names of a node in an ACL resource hash and add them to returned array
     *
     * @param array $resourcesHash
     * @param string $nodeName
     * @return array
     */
    public function addParents(array $resourcesHash, $nodeName)
    {
        $output = [];
        foreach ($resourcesHash as $resource => $children) {
            if ($resource == $nodeName) {
                $output = [$resource];
                break;
            }
            if (!empty($children)) {
                $names = $this->addParents($children, $nodeName);
                if (!empty($names)) {
                    $output = array_merge([$resource], $names);
                    break;
                } else {
                    continue;
                }
            }
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
