<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Generic
 */
class Generic implements LayoutInterface
{
    /**
     * Generate Java Script configuration element
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function build(UiComponentInterface $component)
    {
        $children = [];
        $context = $component->getContext();
        $this->addChildren($children, $component, $component->getName());
        $dataSources = $component->getContext()->getDataSourceData($component);
        $configuration = [
            'types' => $context->getComponentsDefinitions(),
            'components' => [
                $context->getNamespace() => [
                    'children' => array_merge($children, $dataSources)
                ]
            ]
        ];
        return $configuration;
    }

    /**
     * Add children data
     *
     * @param array $topNode
     * @param UiComponentInterface $component
     * @param string $componentType
     * @return void
     */
    protected function addChildren(
        array &$topNode,
        UiComponentInterface $component,
        $componentType
    ) {
        $childrenNode = [];
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            /** @var UiComponentInterface $child */
            foreach ($childComponents as $child) {
                if ($child instanceof DataSourceInterface) {
                    continue;
                }
                self::addChildren($childrenNode, $child, $child->getComponentName());
            }
        }

        $config = $component->getConfiguration();
        if (is_string($config)) {
            $topNode[$config] = $config;
        } else {
            $nodeData = [
                'type' => $componentType,
                'name' => $component->getName(),
            ];
            if (!empty($childrenNode)) {
                $nodeData['children'] = $childrenNode;
            }
            if (isset($config['dataScope'])) {
                $nodeData['dataScope'] = $config['dataScope'];
                unset($config['dataScope']);
            }
            if (!empty($config)) {
                $nodeData['config'] = $config;
            }
            $topNode[$component->getName()] = $nodeData;
        }
    }
}
