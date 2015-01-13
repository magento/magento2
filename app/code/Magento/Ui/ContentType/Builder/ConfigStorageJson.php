<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType\Builder;

use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\ConfigStorageBuilderInterface;
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
            'config' => [],
        ];
        $result['meta'] = $storage->getMeta($parentName);
        $dataSource = $storage->getDataSource($parentName);
        $data = isset($dataSource['data']) ? $dataSource['data'] : null;
        if ($parentName !== null) {
            $rootComponent = $storage->getComponentsData($parentName);
            $result['name'] = $rootComponent->getName();
            $result['parent_name'] = $rootComponent->getParentName();
            $result['data'] = $data;
            $result['config']['components'][$rootComponent->getName()] = $rootComponent->getData();
        } else {
            $components = $storage->getComponentsData();
            if (!empty($components)) {
                /** @var ConfigInterface $component */
                foreach ($components as $name => $component) {
                    $result['config']['components'][$name] = $component->getData();
                }
            }
            $result['data'] = $data;
        }
        $result['config'] += $storage->getGlobalData();
        $result['dump']['extenders'] = [];

        return json_encode($result);
    }

    /**
     * Config storage data to JSON by output
     *
     * @param ConfigStorageInterface $storage
     * @return string
     */
    public function toJsonNew(ConfigStorageInterface $storage)
    {
        $result = [];
        foreach ($storage->getDataSource() as $name => $dataSource) {
            $dataSource['path'] = 'Magento_Ui/js/form/provider';
            $result['providers'][$name] = $dataSource;
        }
        $result['renderer'] = [
            'types' => $storage->getComponents(),
            'layout' => $storage->getLayoutStructure(),
        ];
        return json_encode($result);
    }
}
