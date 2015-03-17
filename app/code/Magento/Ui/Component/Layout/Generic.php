<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout;

use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\JsConfigInterface;
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
        $dataSources = $component->getDataSourceData();
        $configuration = [
            'types' => array_reverse($context->getComponentsDefinitions()),
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
                $this->addChildren($childrenNode, $child, $child->getComponentName());
            }
        }
        /** @var JsConfigInterface $component */
        $config = $component->getJsConfig();
        $nodeData = [
            'type' => $componentType,
            'name' => $component->getName(),
            'dataScope' => $component->getContext()->getNamespace(),
            'children' => $childrenNode
        ];
        if (!empty($config)) {
            $nodeData['config'] = $config;
        }
        $topNode[] = $nodeData;
    }
}
