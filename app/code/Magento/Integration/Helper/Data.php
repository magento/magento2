<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $output = array();
        foreach ($resources as $resource) {
            $item = array();
            $item['attr']['data-id'] = $resource['id'];
            $item['data'] = $resource['title'];
            $item['children'] = array();
            if (isset($resource['children'])) {
                $item['state'] = 'open';
                $item['children'] = $this->mapResources($resource['children']);
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
