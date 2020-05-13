<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Class ColumnFactory. Responsible for the column object generation
 */
class ColumnFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater
     */
    protected $inlineEditUpdater;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'select',
        'date' => 'date',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     * @param InlineEditUpdater $inlineEditor
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        InlineEditUpdater $inlineEditor
    ) {
        $this->componentFactory = $componentFactory;
        $this->inlineEditUpdater = $inlineEditor;
    }

    /**
     * Creates column object for grid ui component
     *
     * @param array $attributeData
     * @param string $columnName
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create(array $attributeData, $columnName, $context, array $config = [])
    {
        $config = array_merge(
            [
                'label' => __($attributeData[AttributeMetadata::FRONTEND_LABEL]),
                'dataType' => $this->getDataType($attributeData[AttributeMetadata::FRONTEND_INPUT]),
                'align' => 'left',
                'visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID],
                'component' => $this->getJsComponent(
                    $this->getDataType($attributeData[AttributeMetadata::FRONTEND_INPUT])
                ),
            ],
            $config
        );
        if ($attributeData[AttributeMetadata::FRONTEND_INPUT] == 'date') {
            $config['dateFormat'] = 'MMM d, y';
            $config['timezone'] = false;
        }
        if (count($attributeData[AttributeMetadata::OPTIONS]) && !isset($config[AttributeMetadata::OPTIONS])) {
            $config[AttributeMetadata::OPTIONS] = $attributeData[AttributeMetadata::OPTIONS];
        }

        if ($attributeData[AttributeMetadata::OPTIONS]) {
            $config['options'] = $attributeData[AttributeMetadata::OPTIONS];
        }
        $arguments = [
            'data' => [
                'js_config' => [
                    'component' => $this->getJsComponent($config['dataType']),
                ],
                'config' => $config,
            ],
            'context' => $context,
        ];
        $column = $this->componentFactory->create($columnName, 'column', $arguments);
        $this->inlineEditUpdater->applyEditing(
            $column,
            $attributeData[AttributeMetadata::FRONTEND_INPUT],
            $attributeData[AttributeMetadata::VALIDATION_RULES],
            $attributeData[AttributeMetadata::REQUIRED]
        );
        return $column;
    }

    /**
     * Returns component map
     *
     * @param string $dataType
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * Returns component map depends on data type
     *
     * @param string $frontendType
     * @return string
     */
    protected function getDataType($frontendType)
    {
        return $this->dataTypeMap[$frontendType] ?? $this->dataTypeMap['default'];
    }
}
