<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Factory\ComponentFactoryInterface;

/**
 * Class UiComponentFactory
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UiComponentFactory extends DataObject
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Argument interpreter
     *
     * @var InterpreterInterface
     */
    protected $argumentInterpreter;

    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * UI component manager
     *
     * @deprecated 100.2.0 since 2.2.0
     * @var ManagerInterface
     */
    protected $componentManager;

    /**
     * @var ComponentFactoryInterface[]
     */
    private $componentChildFactories;

    /**
     * @var DataInterfaceFactory
     */
    private $configFactory;

    /**
     * @var \Magento\Ui\Config\Reader\Definition\Data
     */
    private $definitionData;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $componentManager
     * @param InterpreterInterface $argumentInterpreter
     * @param ContextFactory $contextFactory
     * @param DataInterfaceFactory|null $configFactory
     * @param array $data
     * @param array $componentChildFactories
     * @param DataInterface|null $definitionData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ManagerInterface $componentManager,
        InterpreterInterface $argumentInterpreter,
        ContextFactory $contextFactory,
        array $data = [],
        array $componentChildFactories = [],
        DataInterface $definitionData = null,
        DataInterfaceFactory $configFactory = null
    ) {
        $this->objectManager = $objectManager;
        $this->componentManager = $componentManager;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->contextFactory = $contextFactory;
        $this->componentChildFactories = $componentChildFactories;
        $this->configFactory = $configFactory ?: $this->objectManager->get(DataInterfaceFactory::class);
        parent::__construct($data);
        $this->definitionData = $definitionData ?:
            $this->objectManager->get(DataInterface::class);
    }

    /**
     * Create child components
     *
     * @param array $bundleComponents
     * @param ContextInterface $renderContext
     * @param string $identifier
     * @param array $arguments
     * @return UiComponentInterface
     */
    protected function createChildComponent(
        array &$bundleComponents,
        ContextInterface $renderContext,
        $identifier,
        array $arguments = []
    ) {
        $componentArguments = &$bundleComponents['arguments'];
        list($className, $componentArguments) = $this->argumentsResolver($identifier, $bundleComponents);
        if (isset($componentArguments['data']['disabled']) && (int)$componentArguments['data']['disabled']) {
            return null;
        }

        /**
         * Add an ability to fill component variables from child factory.
         */
        $bundleComponents['components'] = [];
        $components = &$bundleComponents['components'];

        if (isset($this->componentChildFactories[$className])) {
            $factory = $this->componentChildFactories[$className];

            /**
             * Factory return nothing
             * because factory should put created components in the right place
             */
            $factory->create($bundleComponents, $arguments);
        } else {
            foreach ($bundleComponents['children'] as $childrenIdentifier => $childrenData) {
                $children = $this->createChildComponent(
                    $childrenData,
                    $renderContext,
                    $childrenIdentifier,
                    $arguments
                );
                $components[$childrenIdentifier] = $children;
            }
        }
        $components = array_filter($components);
        $componentArguments['components'] = $components;
        if (!isset($componentArguments['context'])) {
            $componentArguments['context'] = $renderContext;
        }

        return $this->objectManager->create($className, $componentArguments);
    }

    /**
     * Resolve arguments
     *
     * @param string $identifier
     * @param array $componentData
     * @return array
     */
    protected function argumentsResolver($identifier, array $componentData)
    {
        $attributes = $componentData[ManagerInterface::COMPONENT_ATTRIBUTES_KEY];
        $className = $attributes['class'];
        unset($attributes['class']);
        $arguments = $componentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY];

        if (!isset($arguments['data'])) {
            $arguments['data'] = [];
        }

        unset($attributes['component']);
        $arguments['data'] = array_merge($arguments['data'], ['name' => $identifier], $attributes);
        return [$className, $arguments];
    }

    /**
     * Create component object
     *
     * @param string $identifier
     * @param string $name
     * @param array $arguments
     * @return UiComponentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function create($identifier, $name = null, array $arguments = [])
    {
        if ($name === null) {
            $componentData = $this->configFactory->create(['componentName' => $identifier])->get($identifier);
            $bundleComponents = [$identifier => $componentData];

            list($className, $componentArguments) = $this->argumentsResolver(
                $identifier,
                $bundleComponents[$identifier]
            );
            $componentArguments = array_replace_recursive($componentArguments, $arguments);
            if (!isset($componentArguments['context'])) {
                $componentArguments['context'] = $this->contextFactory->create(
                    ['namespace' => $identifier]
                );
            }
            $reverseMerge = isset($componentArguments['data']['reverseMetadataMerge'])
                && $componentArguments['data']['reverseMetadataMerge'];
            $bundleComponents = $this->mergeMetadata($identifier, $bundleComponents, $reverseMerge);
            $children = $bundleComponents[$identifier]['children'];
        } else {
            $rawComponentData = $this->definitionData->get($name);
            list($className, $componentArguments) = $this->argumentsResolver($identifier, $rawComponentData);
            $componentArguments = array_replace_recursive($componentArguments, $arguments);
            $children = isset($componentArguments['data']['config']['children']) ?
                        $componentArguments['data']['config']['children'] : [];
            $children = $this->getBundleChildren($children);
        }

        $className = isset($componentArguments['config']['class']) ?
            $componentArguments['config']['class'] : $className;
        $components = [];

        foreach ($children as $childrenIdentifier => $childrenData) {
            $children = $this->createChildComponent(
                $childrenData,
                $componentArguments['context'],
                $childrenIdentifier,
                $arguments
            );
            $components[$childrenIdentifier] = $children;
        }

        $components = array_filter($components);
        $componentArguments['components'] = $components;

        /** @var \Magento\Framework\View\Element\UiComponentInterface $component */
        $component = $this->objectManager->create(
            $className,
            $componentArguments
        );

        return $component;
    }

    /**
     * Get bundle children
     *
     * @param array $children
     * @return array
     * @throws LocalizedException
     * @since 100.1.0
     */
    protected function getBundleChildren(array $children = [])
    {
        $bundleChildren = [];

        foreach ($children as $identifier => $config) {
            if (!isset($config['componentType'])) {
                throw new LocalizedException(new Phrase(
                    'The configuration parameter "componentType" is a required for "%1" component.',
                    $identifier
                ));
            }

            if (!isset($componentArguments['context'])) {
                throw new LocalizedException(new \Magento\Framework\Phrase('Each UI component should have context.'));
            }

            $rawComponentData = $this->definitionData->get($config['componentType']);
            list(, $componentArguments) = $this->argumentsResolver($identifier, $rawComponentData);
            $arguments = array_replace_recursive($componentArguments, ['data' => ['config' => $config]]);
            $rawComponentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY] = $arguments;

            $bundleChildren[$identifier] = $rawComponentData;
            $bundleChildren[$identifier]['children'] = [];

            if (isset($arguments['data']['config']['children'])) {
                $bundleChildren[$identifier]['children'] = $this->getBundleChildren(
                    $arguments['data']['config']['children']
                );
            }
        }

        return $bundleChildren;
    }

    /**
     * Merge data provider's metadata to components
     *
     * @param string $identifier
     * @param array $bundleComponents
     * @param bool $reverseMerge
     * @return array
     * @since 100.1.0
     */
    protected function mergeMetadata($identifier, array $bundleComponents, $reverseMerge = false)
    {
        $dataProvider = $this->getDataProvider($identifier, $bundleComponents);
        if ($dataProvider instanceof DataProviderInterface) {
            $metadata = [
                $identifier => [
                    'children' => $dataProvider->getMeta(),
                ],
            ];
            $bundleComponents = $this->mergeMetadataItem($bundleComponents, $metadata, $reverseMerge);
        }

        return $bundleComponents;
    }

    /**
     * Find element in components or its containers and merge data to it
     *
     * @param array $bundleComponents
     * @param string $name
     * @param array $data
     * @param bool $reverseMerge
     * @return array
     * @since 100.1.0
     */
    protected function mergeMetadataElement(array $bundleComponents, $name, array $data, $reverseMerge = false)
    {
        if (isset($bundleComponents[$name])) {
            $bundleComponents[$name] = $reverseMerge
                ? array_replace_recursive($data, $bundleComponents[$name])
                : array_replace_recursive($bundleComponents[$name], $data);
            return [$bundleComponents, true];
        } else {
            foreach ($bundleComponents as &$childData) {
                if (isset($childData['attributes']['class'])
                    && is_a($childData['attributes']['class'], \Magento\Ui\Component\Container::class, true)
                    && isset($childData['children']) && is_array($childData['children'])
                ) {
                    list($childData['children'], $isMerged) = $this->mergeMetadataElement(
                        $childData['children'],
                        $name,
                        $data,
                        $reverseMerge
                    );
                    if ($isMerged) {
                        return [$bundleComponents, true];
                    }
                }
            }
        }
        return [$bundleComponents, false];
    }

    /**
     * Merge metadata item to components
     *
     * @param array $bundleComponents
     * @param array $metadata
     * @param bool $reverseMerge
     * @return array
     * @throws LocalizedException
     * @since 100.1.0
     */
    protected function mergeMetadataItem(array $bundleComponents, array $metadata, $reverseMerge = false)
    {
        foreach ($metadata as $name => $data) {
            $selfData = $data;
            if (isset($selfData['children'])) {
                unset($selfData['children']);
            }

            list($bundleComponents, $isMerged) = $this->mergeMetadataElement(
                $bundleComponents,
                $name,
                $selfData,
                $reverseMerge
            );

            if (!$isMerged) {
                if (!isset($data['arguments']['data']['config']['componentType'])) {
                    throw new LocalizedException(new Phrase(
                        'The configuration parameter "componentType" is a required for "%1" component.',
                        [$name]
                    ));
                }
                $rawComponentData = $this->definitionData->get(
                    $data['arguments']['data']['config']['componentType']
                );
                list(, $componentArguments) = $this->argumentsResolver($name, $rawComponentData);
                $arguments = array_replace_recursive($componentArguments, $data['arguments']);
                $rawComponentData[ManagerInterface::COMPONENT_ARGUMENTS_KEY] = $arguments;

                $bundleComponents[$name] = $rawComponentData;
                $bundleComponents[$name]['children'] = [];
            }

            if (isset($data['children']) && is_array($data['children'])) {
                $bundleComponents[$name]['children'] = $this->mergeMetadataItem(
                    $bundleComponents[$name]['children'],
                    $data['children'],
                    $reverseMerge
                );
            }
        }

        return $bundleComponents;
    }

    /**
     * Find and return data provider
     *
     * @param string $identifier
     * @param array $bundleComponents
     * @return DataProviderInterface|null
     * @since 100.1.0
     */
    protected function getDataProvider($identifier, array $bundleComponents)
    {
        foreach ($bundleComponents[$identifier]['children'] as $childrenData) {
            if (isset($childrenData['arguments']['dataProvider'])
                && $childrenData['arguments']['dataProvider'] instanceof DataProviderInterface
            ) {
                return $childrenData['arguments']['dataProvider'];
            }
        }

        return null;
    }
}
