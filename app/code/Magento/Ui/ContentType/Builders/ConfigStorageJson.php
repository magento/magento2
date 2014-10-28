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
namespace Magento\Ui\ContentType\Builders;

use Magento\Framework\View\Element\UiComponent\ConfigStorageBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\ConfigStorageInterface;

/**
 * Class ConfigStorageBuilder
 */
class ConfigStorageJson implements ConfigStorageBuilderInterface
{
    /**
     * Config storage data to JSON by output
     *
     * @param ConfigStorageInterface $storage
     * @param string $parentName
     * @return string
     */
    public function toJson(ConfigStorageInterface $storage, $parentName = null)
    {
        $result = [
            'config' => []
        ];
        $result['meta'] = $storage->getMeta($parentName);
        if ($parentName !== null) {
            $rootComponent = $storage->getComponentsData($parentName);
            $result['name'] = $rootComponent->getName();
            $result['parent_name'] = $rootComponent->getParentName();
            $result['data'] = $storage->getData($parentName);
            $result['config']['components'][$rootComponent->getName()] = $rootComponent->getData();
        } else {
            $components = $storage->getComponentsData();
            if (!empty($components)) {
                /** @var ConfigInterface $component */
                foreach ($components as $name => $component) {
                    $result['config']['components'][$name] = $component->getData();
                }
            }
            $result['data'] = $storage->getData();
        }

        $result['config'] += $storage->getGlobalData();
        $result['dump']['extenders'] = [];

        return json_encode($result);
    }
}
