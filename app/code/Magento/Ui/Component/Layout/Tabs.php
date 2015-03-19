<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Layout
 */
class Tabs extends Generic implements LayoutInterface
{
    /**
     * @var string
     */
    protected $navContainerName;

    /**
     * @var UiComponentInterface
     */
    protected $component;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $structure = [];

    /**
     * @var int
     */
    protected $sortIncrement = 10;

    public function __construct($navContainerName = null)
    {
        $this->navContainerName = $navContainerName;
    }

    /**
     * @param UiComponentInterface $component
     * @return array
     */
    public function build(UiComponentInterface $component)
    {
        $this->component = $component;
        $this->namespace = $component->getContext()->getNamespace();

        $this->addNavigationBlock();
        return parent::build($component);
    }

    protected function addNavigationBlock()
    {
        $pageLayout = $this->component->getContext()->getPageLayout();
        /** @var \Magento\Ui\Component\Layout\Tabs\Nav $navBlock */
        if ($this->navContainerName) {
            $navBlock = $pageLayout->addBlock('Magento\Ui\Component\Layout\Tabs\Nav', 'tabs_nav', $this->navContainerName);
        } else {
            $navBlock = $pageLayout->addBlock('Magento\Ui\Component\Layout\Tabs\Nav', 'tabs_nav', 'content');
        }
        $navBlock->setTemplate('Magento_Ui::layout/tabs/nav/default.phtml');
        $navBlock->setData('data_scope', $this->namespace);
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
        $this->initSections();
        $this->initAreas();
        $this->initGroups();
        $this->initElements();

        $this->processDataSource();

        $this->processChildBlocks();

        $topNode = $this->structure;
    }

    /**
     * Prepare initial structure for sections
     *
     * @return void
     */
    protected function initSections()
    {
        $this->structure[static::SECTIONS_KEY] = [
            'type' => 'nav',
            'config' => [
                'label' => $this->component->getData('label'),
            ],
            'children' => [],
        ];
    }

    /**
     * Prepare initial structure for areas
     *
     * @return void
     */
    protected function initAreas()
    {
        $this->structure[static::AREAS_KEY] = [
            'type' => 'form',
            'config' => [
                'namespace' => $this->namespace,
            ],
            'children' => [],
        ];
    }

    /**
     * Prepare initial structure for groups
     *
     * @return void
     */
    protected function initGroups()
    {
        $this->structure[static::GROUPS_KEY] = [
            'children' => [],
        ];
    }

    /**
     * Prepare initial structure for elements
     *
     * @return void
     */
    protected function initElements()
    {
        $this->structure[static::ELEMENTS_KEY] = [
            'children' => [],
        ];
    }

    /**
     * Process data source
     *
     * @return array
     */
    protected function processDataSource()
    {
        $dataProvider = $this->component->getContext()->getDataProvider();

        foreach ($dataProvider->getMeta() as $name => $meta) {
            $areName = $groupName = $name;
            $areaConfig = $groupConfig = [
                'config' => isset($meta['config']) ? $meta['config'] : []
            ];
            $areaConfig['insertTo'] = [
                "{$this->namespace}.sections" => ['position' => $this->getNextSortIncrement()]
            ];
            $this->addArea($areName, $areaConfig);

            $groupReferenceName = $this->addGroup($groupName, $groupConfig);
            $this->addToArea($name, $groupReferenceName);
            $fieldSet = $this->component->getComponent($name);
            if (!$fieldSet) {
                continue;
            }

            $elements = $fieldSet->getChildComponents();
            uasort($elements, [$this, 'sortChildren']);

            $collection = & $this->structure[static::ELEMENTS_KEY];

            if (isset($meta['is_collection'])) {
                $templateGroupName = $groupName . '_template';
                $groupConfig['type'] = 'collection';
                $groupConfig['dataScope'] = "{$this->namespace}.{$name}";
                $groupConfig['config']['active'] = 1;
                $groupConfig['config']['removeLabel'] = __('Remove ' . $groupConfig['config']['label']);
                $groupConfig['config']['removeMessage'] = __('Are you sure you want to delete this item?');
                $groupConfig['config']['addLabel'] = __('Add New ' . $groupConfig['config']['label']);
                $groupConfig['config']['itemTemplate'] = 'item_template';

                $itemTemplate = [
                    'type' => $this->namespace,
                    'isTemplate' => true,
                    'component' => 'Magento_Ui/js/form/components/collection/item',
                    'childType' => 'group',
                    'config' => [
                        'label' => __('New ' . $groupConfig['config']['label']),
                    ],
                ];

                foreach ($elements as $elementName => $component) {
                    if ($component instanceof DataSourceInterface) {
                        continue;
                    }
                    $visibility = $component->getData('visible');
                    if (isset($visibility) && $visibility === 'false') {
                        continue;
                    }

                    $this->addToCollection($itemTemplate, $elementName, "{$this->namespace}.{$elementName}", $component->getData());

                    $referenceName = "{$name}.elements.{$elementName}";
                    $this->addToGroup($templateGroupName, $elementName, $referenceName, $component->getData());
                }
                $groupConfig['children']['item_template'] = $itemTemplate;
                $templateGroupReferenceName = $this->addGroup($templateGroupName, $groupConfig);
                $this->addToGroup($groupName, $templateGroupName, $templateGroupReferenceName);
            } else {
                foreach ($elements as $elementName => $component) {
                    if ($component instanceof DataSourceInterface) {
                        continue;
                    }
                    $visibility = $component->getData('config/visible');
                    if (isset($visibility) && $visibility === 'false') {
                        continue;
                    }

                    $this->addToCollection($collection, $elementName, "{$this->namespace}.{$elementName}", $component->getData());

                    $referenceName = "{$name}.elements.{$elementName}";
                    $this->addToGroup($groupName, $elementName, $referenceName, $component->getData());
                }
            }
        }
    }

    /**
     * Process child blocks
     *
     * @throws \Exception
     * @return void
     */
    protected function processChildBlocks()
    {
        // Add child blocks content
        foreach ($this->component->getChildComponents() as $blockName => $childBlock) {
            /** @var BlockInterface $childBlock */
            if ($childBlock instanceof UiComponentInterface) {
                continue;
            }
            /** @var TabInterface $childBlock */
            if (!($childBlock instanceof TabInterface)) {
                throw new \Exception(__('"%1" tab should implement TabInterface', $blockName));
            }
            if (!$childBlock->canShowTab()) {
                continue;
            }
            $childBlock->setData('target_form', $this->namespace);
            $sortOrder = $childBlock->hasSortOrder() ? $childBlock->getSortOrder() : $this->getNextSortIncrement();
            $this->addArea(
                $blockName,
                [
                    'insertTo' => [
                        "{$this->namespace}.sections" => ['position' => (int)$sortOrder]
                    ],
                    'config' => ['label' => $childBlock->getTabTitle()]
                ]
            );

            $config = [
                'config' => [
                    'label' => $childBlock->getTabTitle()
                ]
            ];
            if ($childBlock->isAjaxLoaded()) {
                $config['config']['source'] = $childBlock->getTabUrl();
            } else {
                $config['config']['content'] = $childBlock->toHtml();
            }
            $config['type'] = 'html_content';
            $referenceGroupName = $this->addGroup($blockName, $config);
            $this->addToArea($blockName, $referenceGroupName);
        }
    }

    /**
     * Add area
     *
     * @param string $name
     * @param array $config
     * @return string
     */
    public function addArea($name, array $config = [])
    {
        $config['type'] = 'tab';
        $this->structure[static::AREAS_KEY]['children'][$name] = $config;

        return "{$this->namespace}.areas.{$name}";
    }

    /**
     * Add item to area
     *
     * @param string $areaName
     * @param string $itemName
     * @return void
     */
    public function addToArea($areaName, $itemName)
    {
        $this->structure[static::AREAS_KEY]['children'][$areaName]['children'][] = $itemName;
    }

    /**
     * Add group
     *
     * @param string $groupName
     * @param array $config
     * @return string
     */
    public function addGroup($groupName, array $config = [])
    {
        $this->structure[static::GROUPS_KEY]['children'][$groupName] = $config;

        return "{$this->namespace}.groups.{$groupName}";
    }

    /**
     * Add element to group
     *
     * @param string $groupName
     * @param string $elementName
     * @param string $referenceElementName
     * @param array $element
     * @return void
     */
    public function addToGroup($groupName, $elementName, $referenceElementName, array $element = [])
    {
        $groups = & $this->structure[static::GROUPS_KEY];
        if (isset($element['fieldGroup'])) {
            if ($elementName === $element['fieldGroup']) {
                $groups['children'][$groupName]['children'][] = $referenceElementName;
            }
        } else {
            $groups['children'][$groupName]['children'][] = $referenceElementName;
        }
    }

    /**
     * Add collection
     *
     * @param string $collectionName
     * @param string $dataScope
     * @param array $config
     * @return string
     */
    public function addCollection($collectionName, $dataScope, array $config = [])
    {
        $this->structure[static::GROUPS_KEY]['children'][$collectionName] = [
            'type' => 'collection',
            'dataScope' => $dataScope,
            'config' => $config,
        ];

        return "{$this->namespace}.groups.{$collectionName}";
    }

    /**
     * Add element to collection
     *
     * @param array $collection
     * @param string $elementName
     * @param string $dataScope
     * @param array $element
     * @return void
     */
    public function addToCollection(array & $collection, $elementName, $dataScope, array $element)
    {
        $collection['children'][$elementName] = ['type' => 'group'];

        if (isset($element['fieldGroup'])) {
            $elementName = $element['fieldGroup'];
            if (isset($element['displayArea']) && $elementName === $element['fieldGroup']) {
                $collection['children'][$elementName]['config'] = ['displayArea' => $element['displayArea']];
            }
        } else {
            if (isset($element['displayArea'])) {
                $collection['children'][$elementName]['config'] = ['displayArea' => $element['displayArea']];
            }
        }

        if (isset($element['constraints'])) {
            if (isset($element['constraints']['validate'])) {
                $element['validation'] = $element['constraints']['validate'];
            }
            if (isset($element['constraints']['filter'])) {
                foreach ($element['constraints']['filter'] as $filter) {
                    $element['listeners'] = [
                        "data:{$filter['on']}" => ['filter' => [$filter['by']]],
                    ];
                }
            }
            unset($element['constraints']);
        }

        if (isset($element['size'])) {
            $collection['children'][$elementName]['dataScope'] = $dataScope;
            $size = (int) @$element['size'];
            for ($i = 0; $i < $size; ++$i) {
                $collection['children'][$elementName]['children'][] = [
                    'type' => @$element['formElement'],
                    'dataScope' => strval($i),
                    'config' => $element,
                ];
                if (isset($element['validation']['required-entry'])) {
                    unset($element['validation']['required-entry']);
                }
            }
        } else {
            $collection['children'][$elementName]['children'][] = [
                'type' => @$element['formElement'],
                'dataScope' => $dataScope,
                'config' => $element,
            ];
        }
    }

    /**
     * Add template to collection
     *
     * @param string $collectionName
     * @param string $templateName
     * @param array $template
     * @return void
     */
    protected function addTemplateToCollection($collectionName, $templateName, $template)
    {
        $groups = & $this->structure[static::GROUPS_KEY];
        $groups['children'][$collectionName]['children'][$templateName] = $template;
    }

    /**
     * Get next sort increment
     *
     * @return int
     */
    protected function getNextSortIncrement()
    {
        $this->sortIncrement += 10;

        return $this->sortIncrement;
    }

    /**
     * Sort child elements
     *
     * @param UiComponentInterface $one
     * @param UiComponentInterface $two
     * @return int
     */
    public function sortChildren(UiComponentInterface $one, UiComponentInterface $two)
    {
        if (!$one->getData('config/sortOrder')) {
            return 1;
        }
        if (!$two->getData('config/sortOrder')) {
            return -1;
        }
        return intval($one->getData('config/sortOrder')) - intval($two->getData('config/sortOrder'));
    }
}
