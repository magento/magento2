<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Customer\Api\CustomerMetadataInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /** @var int */
    protected $columnSortOrder;

    /** @var AttributeRepository  */
    protected $attributeRepository;

    /** @var InlineEditUpdater */
    protected $inlineEditUpdater;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @param ContextInterface $context
     * @param ColumnFactory $columnFactory
     * @param AttributeRepository $attributeRepository
     * @param InlineEditUpdater $inlineEditor
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        AttributeRepository $attributeRepository,
        InlineEditUpdater $inlineEditor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->inlineEditUpdater = $inlineEditor;
    }

    /**
     * @return int
     */
    protected function getDefaultSortOrder()
    {
        $max = 0;
        foreach ($this->components as $component) {
            $config = $component->getData('config');
            if (isset($config['sortOrder']) && $config['sortOrder'] > $max) {
                $max = $config['sortOrder'];
            }
        }
        return ++$max;
    }

    /**
     * Update actions column sort order
     *
     * @return void
     */
    protected function updateActionColumnSortOrder()
    {
        if (isset($this->components['actions'])) {
            $component = $this->components['actions'];
            $component->setData(
                'config',
                array_merge($component->getData('config'), ['sortOrder' => ++$this->columnSortOrder])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->columnSortOrder = $this->getDefaultSortOrder();
        foreach ($this->attributeRepository->getList() as $newAttributeCode => $attributeData) {
            if (isset($this->components[$newAttributeCode])) {
                $this->updateColumn($attributeData, $newAttributeCode);
            } elseif (!$attributeData[AttributeMetadata::BACKEND_TYPE] != 'static'
                && $attributeData[AttributeMetadata::IS_USED_IN_GRID]
            ) {
                $this->addColumn($attributeData, $newAttributeCode);
            }
        }
        $this->updateActionColumnSortOrder();
        parent::prepare();
    }

    /**
     * @param array $attributeData
     * @param string $columnName
     * @return void
     */
    public function addColumn(array $attributeData, $columnName)
    {
        $config['sortOrder'] = ++$this->columnSortOrder;
        if ($attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]) {
            $config['filter'] = $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]);
        }
        $column = $this->columnFactory->create($attributeData, $columnName, $this->getContext(), $config);
        $column->prepare();
        $this->addComponent($attributeData[AttributeMetadata::ATTRIBUTE_CODE], $column);
    }

    /**
     * @param array $attributeData
     * @param string $newAttributeCode
     * @return void
     */
    public function updateColumn(array $attributeData, $newAttributeCode)
    {
        $component = $this->components[$attributeData[AttributeMetadata::ATTRIBUTE_CODE]];
        $this->addOptions($component, $attributeData);

        if ($attributeData[AttributeMetadata::BACKEND_TYPE] != 'static') {
            if ($attributeData[AttributeMetadata::IS_USED_IN_GRID]) {
                $config = array_merge(
                    $component->getData('config'),
                    [
                        'name' => $newAttributeCode,
                        'dataType' => $attributeData[AttributeMetadata::BACKEND_TYPE],
                        'visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID]
                    ]
                );
                if ($attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]) {
                    $config['filter'] = $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]);
                }
                $component->setData('config', $config);
            }
        } else {
            if ($attributeData['entity_type_code'] == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER
                && !empty($component->getData('config')['editor'])
            ) {
                $this->inlineEditUpdater->applyEditing(
                    $component,
                    $attributeData[AttributeMetadata::FRONTEND_INPUT],
                    $attributeData[AttributeMetadata::VALIDATION_RULES],
                    $attributeData[AttributeMetadata::REQUIRED]
                );
            }
            $component->setData(
                'config',
                array_merge(
                    $component->getData('config'),
                    ['visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID]]
                )
            );
        }
    }

    /**
     * Add options to component
     *
     * @param UiComponentInterface $component
     * @param array $attributeData
     * @return void
     */
    public function addOptions(UiComponentInterface $component, array $attributeData)
    {
        $config = $component->getData('config');
        if (count($attributeData[AttributeMetadata::OPTIONS]) && !isset($config[AttributeMetadata::OPTIONS])) {
            $component->setData(
                'config',
                array_merge($config, [AttributeMetadata::OPTIONS => $attributeData[AttributeMetadata::OPTIONS]])
            );
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
