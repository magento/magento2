<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\BlockWrapperInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Generic
 */
class Generic implements LayoutInterface
{
    const CONFIG_JS_COMPONENT = 'component';
    const CONFIG_COMPONENT_NAME = 'componentName';
    const CONFIG_PANEL_COMPONENT = 'panelComponentName';

    /**
     * @var UiComponentInterface
     * @since 2.1.0
     */
    protected $component;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $namespace;

    /**
     * @var UiComponentFactory
     * @since 2.1.0
     */
    protected $uiComponentFactory;

    /**
     * @var array
     * @since 2.1.0
     */
    protected $data;

    /**
     * @param UiComponentFactory $uiComponentFactory
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(UiComponentFactory $uiComponentFactory, $data = [])
    {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->data = $data;
    }

    /**
     * Generate Java Script configuration element
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function build(UiComponentInterface $component)
    {
        $this->component = $component;
        $this->namespace = $component->getContext()->getNamespace();

        $this->component->getContext()->addComponentDefinition(
            $this->getConfig(self::CONFIG_COMPONENT_NAME),
            [
                'component' => $this->getConfig(self::CONFIG_JS_COMPONENT),
                'extends' => $this->namespace
            ]
        );

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
                if ($child->getData('wrapper')) {
                    $this->addWrappedBlock($child, $childrenNode);
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

    /**
     * Add wrapped layout block
     *
     * @param BlockWrapperInterface $childComponent
     * @param array $childrenNode
     * @return $this
     * @since 2.1.0
     */
    protected function addWrappedBlock(BlockWrapperInterface $childComponent, array &$childrenNode)
    {
        if (!($childComponent->getData('wrapper/canShow') && $childComponent->getData('wrapper/componentType'))) {
            return $this;
        }

        $name = $childComponent->getName();
        $panelComponent = $this->createChildFormComponent($childComponent, $name);
        $childrenNode[$name] = [
            'type' => $panelComponent->getComponentName(),
            'dataScope' => $name,
            'config' => $childComponent->getConfiguration(),
            'children' => [
                $name => [
                    'type' => $childComponent->getComponentName(),
                    'dataScope' => $name,
                    'config' => [
                        'content' => $childComponent->render()
                    ],
                ]
            ],
        ];

        return $this;
    }

    /**
     * Create child of form
     *
     * @param UiComponentInterface $childComponent
     * @param string $name
     * @return UiComponentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    protected function createChildFormComponent(UiComponentInterface $childComponent, $name)
    {
        $panelComponent = $this->uiComponentFactory->create(
            $name,
            $childComponent->getData('wrapper/componentType'),
            [
                'context' => $this->component->getContext(),
                'components' => [$childComponent->getName() => $childComponent]
            ]
        );
        $panelComponent->prepare();
        $this->component->addComponent($name, $panelComponent);

        return $panelComponent;
    }

    /**
     * Get config by name
     *
     * @param string $name
     * @return mixed
     * @since 2.1.0
     */
    protected function getConfig($name)
    {
        return isset($this->data['config'][$name]) ? $this->data['config'][$name] : null;
    }
}
