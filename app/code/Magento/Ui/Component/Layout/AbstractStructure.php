<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Layout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiElementFactory;
use Magento\Ui\Component\AbstractView;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\DataProvider\Manager;
use Magento\Ui\DataProvider\Metadata;

/**
 * Class AbstractStructure
 */
class AbstractStructure extends AbstractView
{
    /**
     * @var array
     */
    protected $structure = [
        'sections' => [],
        'areas' => [],
        'groups' => [],
        'elements' => [],
    ];

    /**
     * @var UiElementFactory
     */
    protected $factory;

    /**
     * Layout Namespace
     *
     * @var string
     */
    protected $ns;

    /**
     * @var int
     */
    protected $sortInc = 10;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param DataProviderFactory $dataProviderFactory
     * @param Manager $dataProviderManager
     * @param UiElementFactory $factory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        DataProviderFactory $dataProviderFactory,
        Manager $dataProviderManager,
        UiElementFactory $factory,
        array $data = []
    ) {
        $this->factory = $factory;
        parent::__construct(
            $context,
            $renderContext,
            $contentTypeFactory,
            $configFactory,
            $configBuilder,
            $dataProviderFactory,
            $dataProviderManager,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $this->ns = $this->getData('name');

        $this->initSections();
        $this->initAreas();
        $this->initGroups();
        $this->initElements();

        foreach ($this->getDataSources() as $name => $dataSourceConfig) {
            $this->processDataSource($dataSourceConfig);
        }

        $this->processChildBLocks();

        $this->renderContext->getStorage()->addLayoutStructure(
            $this->getDataScope(),
            [
                'children' => $this->structure
            ]
        );

        $navBlock = $this->factory->create(
            \Magento\Ui\Component\Layout\Tabs\Nav::NAME,
            [
                'data_scope' => $this->ns
            ]
        );
        if ($this->getData('configuration/tabs_container_name')) {
            $this->getRenderContext()->getPageLayout()
                ->addBlock($navBlock, 'tabs_nav', $this->getData('configuration/tabs_container_name'));
        } else {
            $this->getRenderContext()->getPageLayout()
                ->addBlock($navBlock, 'tabs_nav', 'content');
        }
    }

    /**
     * @return string
     */
    public function getDataScope()
    {
        return $this->ns;
    }

    /**
     * Prepare initial structure for sections
     *
     * @return void
     */
    protected function initSections()
    {
        $this->structure['sections'] = [
            'type' => \Magento\Ui\Component\Layout\Tabs\Nav::NAME,
            'config' => [
                'label' => $this->getData('label'),
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
        $this->structure['areas'] = [
            'type' => 'form',
            'config' => [
                'namespace' => $this->ns,
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
        $this->structure['groups'] = [
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
        $this->structure['elements'] = [
            'children' => [],
        ];
    }

    /**
     * Get registered Data Sources
     *
     * @return array
     */
    protected function getDataSources()
    {
        return $this->getData('data_sources');
    }

    /**
     * @param array $dataSourceConfig
     * @return void
     */
    protected function processDataSource(array $dataSourceConfig)
    {
        $id = $this->renderContext->getRequestParam('id');
        $dataSource = $dataSourceConfig['name'];

        $meta = $this->dataManager->getMetadata($dataSource);

        $this->addArea(
            $dataSource,
            [
                'insertTo' => [
                    $this->ns . '.sections' => [
                        'position' => $this->getNextSortInc(),
                    ],
                ],
                'config' => [
                    'label' => $meta->getLabel(),
                ]
            ]
        );
        $referenceGroupName = $this->addGroup(
            $dataSource,
            [
                'label' => $meta->getLabel()
            ]
        );

        $elements = $meta->getFields();
        uasort($elements, [$this, 'sortChildren']);
        foreach ($elements as $name => $element) {
            if (isset($element['visible']) && $element['visible'] === 'false') {
                continue;
            }
            if ($name != Metadata::CHILD_DATA_SOURCES) {
                $collection = & $this->structure['elements'];
                $this->addElementToCollection($collection, $name, "{$dataSource}.{$name}", $element);

                $referenceName = "{$this->ns}.elements.{$name}";
                $this->addElementToGroup($dataSource, $name, $referenceName, $element);
            }
        }

        $this->addToArea($dataSource, $referenceGroupName);

        $children = $meta->get(Metadata::CHILD_DATA_SOURCES);
        foreach ($children as $childName => $childMeta) {
            $this->processChildDataSource($dataSource, $childName, $childMeta);
        }

        $preparedData = [];
        $data = $id ? $this->dataManager->getData($dataSource, ['entity_id' => $id]) : [];
        if ($data) {
            $preparedData[$dataSource] = [];
            foreach (array_shift($data) as $key => $value) {
                $preparedData[$dataSource][$key] = $value;
            }
        }

        $this->renderContext->getStorage()->addDataSource(
            $this->getData('name'),
            [
                'data' => $preparedData,
                'config' => $dataSourceConfig,
            ]
        );
    }

    /**
     * @param string $dataSource
     * @param string $childName
     * @param \Magento\Ui\DataProvider\Metadata $childMeta
     * @return void
     */
    protected function processChildDataSource($dataSource, $childName, \Magento\Ui\DataProvider\Metadata $childMeta)
    {
        $this->addArea(
            $childName,
            [
                'insertTo' => [
                    $this->ns . '.sections' => [
                        'position' => $this->getNextSortInc(),
                    ],
                ],
                'config' => [
                    'label' => $childMeta->getLabel(),
                ]
            ]
        );
        $referenceChildGroupName = $this->addGroup(
            $childName,
            [
                'label' => $childMeta->getLabel()
            ]
        );
        $this->addToArea($childName, $referenceChildGroupName);

        $itemTemplate = [
            'type' => $this->ns,
            'isTemplate' => true,
            'component' => 'Magento_Ui/js/form/components/collection/item',
            'childType' => 'group',
            'config' => [
                'label' => __('New ' . $childMeta->getLabel()),
            ],
        ];

        $elements = $childMeta->getFields();
        uasort($elements, [$this, 'sortChildren']);
        foreach ($elements as $name => $element) {
            if (isset($element['visible']) && $element['visible'] === 'false') {
                continue;
            }
            $this->addElementToCollection($itemTemplate, $name, $name, $element);
        }

        $referenceCollectionName = $this->addCollection(
            $childName . 'Collection',
            "{$dataSource}.{$childName}",
            [
                'active' => 1,
                'label' => $childMeta->getLabel(),
                'removeLabel' => __('Remove ' . $childMeta->getLabel()),
                'removeMessage' => __('Are you sure you want to delete this item?'),
                'addLabel' => __('Add New ' . $childMeta->getLabel()),
                'itemTemplate' => 'item_template'
            ]
        );
        $this->addTemplateToCollection($childName . 'Collection', 'item_template', $itemTemplate);

        $this->structure['groups']['children'][$childName]['children'][] = $referenceCollectionName;
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function processChildBlocks()
    {
        //Add child blocks content
        foreach ($this->getData('child_blocks') as $blockName => $childBlock) {
            /** @var TabInterface $childBlock */
            if (!($childBlock instanceof TabInterface)) {
                throw new \Exception(__('"%1" tab should implement TabInterface', $blockName));
            }
            if (!$childBlock->canShowTab()) {
                continue;
            }
            $childBlock->setData('target_form', $this->getDataScope());
            $sortOrder = $childBlock->hasSortOrder() ? $childBlock->getSortOrder() : $this->getNextSortInc();
            $this->addArea(
                $blockName,
                [
                    'insertTo' => [
                        $this->ns . '.sections' => [
                            'position' => (int)$sortOrder,
                        ],
                    ],
                    'config' => [
                        'label' => $childBlock->getTabTitle(),
                    ]
                ]
            );

            $config = [
                'label' => $childBlock->getTabTitle(),
            ];
            if ($childBlock->isAjaxLoaded()) {
                $config['source'] = $childBlock->getTabUrl();
            } else {
                $config['content'] = $childBlock->toHtml();
            }
            $referenceGroupName = $this->addGroup($blockName, $config, 'html_content');
            $this->addToArea($blockName, $referenceGroupName);
        }
    }

    /**
     * @param string $name
     * @param array $config
     * @return string
     */
    protected function addArea($name, array $config = [])
    {
        $config['type'] = 'tab';
        $this->structure['areas']['children'][$name] = $config;
        return "{$this->ns}.areas.{$name}";
    }

    /**
     * @param string $areaName
     * @param string $itemName
     * @return void
     */
    protected function addToArea($areaName, $itemName)
    {
        $this->structure['areas']['children'][$areaName]['children'][] = $itemName;
    }

    /**
     * @param string $groupName
     * @param array $config
     * @param string $type
     * @return string
     */
    protected function addGroup($groupName, array $config = [], $type = 'fieldset')
    {
        $this->structure['groups']['children'][$groupName] = [
            'type' => $type,
            'config' => $config,
        ];
        return "{$this->ns}.groups.{$groupName}";
    }

    /**
     * @param string $groupName
     * @param string $elementName
     * @param string $referenceElementName
     * @param array $element
     * @return void
     */
    protected function addElementToGroup($groupName, $elementName, $referenceElementName, array $element)
    {
        if (isset($element['fieldGroup'])) {
            if ($elementName === $element['fieldGroup']) {
                $this->structure['groups']['children'][$groupName]['children'][] = $referenceElementName;
            }
        } else {
            $this->structure['groups']['children'][$groupName]['children'][] = $referenceElementName;
        }
    }

    /**
     * @param array $collection
     * @param string $elementName
     * @param string $dataScope
     * @param array $element
     * @return string
     */
    protected function addElementToCollection(array & $collection, $elementName, $dataScope, array $element)
    {
        $collection['children'][$elementName] = [
            'type' => 'group',
        ];
        if (isset($element['fieldGroup'])) {
            $elementName = $element['fieldGroup'];
            if ($elementName === $element['fieldGroup']) {
                $collection['children'][$elementName]['config'] = [
                    'displayArea' => $element['displayArea'],
                ];
            }
        } else {
            $collection['children'][$elementName]['config'] = [
                'displayArea' => $element['displayArea'],
            ];
        }

        if (isset($element['constraints'])) {
            if (isset($element['constraints']['validate'])) {
                $element['validation'] = $element['constraints']['validate'];
            }
            if (isset($element['constraints']['filter'])) {
                foreach ($element['constraints']['filter'] as $filter) {
                    $element['listeners'] = [
                        "data:" . $filter['on'] => [
                            'filter' => [$filter['by']],
                        ],
                    ];
                }
            }
            unset($element['constraints']);
        }
        if (isset($element['size'])) {
            $collection['children'][$elementName]['dataScope'] = $dataScope;
            $size = (int)$element['size'];
            for ($i = 0; $i < $size; $i++) {
                $collection['children'][$elementName]['children'][] = [
                    'type' => $element['formElement'],
                    'dataScope' => (string)$i,
                    'config' => $element,
                ];
                if (isset($element['validation']['required-entry'])) {
                    unset($element['validation']['required-entry']);
                }
            }
        } else {
            $collection['children'][$elementName]['children'][] = [
                'type' => $element['formElement'],
                'dataScope' => $dataScope,
                'config' => $element,
            ];
        }
    }

    /**
     * @param string $collectionName
     * @param string $dataScope
     * @param array $config
     * @return string
     */
    protected function addCollection($collectionName, $dataScope, array $config = [])
    {
        $this->structure['groups']['children'][$collectionName] = [
            'type' => 'collection',
            'dataScope' => $dataScope,
            'config' => $config,
        ];
        return "{$this->ns}.groups.{$collectionName}";
    }

    /**
     * @param string $collectionName
     * @param string $templateName
     * @param array $template
     * @return void
     */
    protected function addTemplateToCollection($collectionName, $templateName, $template)
    {
        $this->structure['groups']['children'][$collectionName]['children'][$templateName] = $template;
    }

    /**
     * @return int
     */
    protected function getNextSortInc()
    {
        $this->sortInc += 10;
        return $this->sortInc;
    }

    /**
     * Sort child elements
     *
     * @param array $one
     * @param array $two
     * @return int
     */
    public function sortChildren(array $one, array $two)
    {
        if (!isset($one['sortOrder'])) {
            return 1;
        }
        if (!isset($two['sortOrder'])) {
            return -1;
        }
        $sortOrderA = isset($one['sortOrder']) ? intval($one['sortOrder']) : -1;
        $sortOrderB = isset($two['sortOrder']) ? intval($two['sortOrder']) : -1;
        if ($sortOrderA == $sortOrderB) {
            return 0;
        }
        return ($sortOrderA < $sortOrderB) ? -1 : 1;
    }
}
