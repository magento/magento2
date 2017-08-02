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
 * Class \Magento\Customer\Ui\Component\ColumnFactory
 *
 * @since 2.0.0
 */
class ColumnFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     * @since 2.0.0
     */
    protected $componentFactory;

    /**
     * @var \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater
     * @since 2.0.0
     */
    protected $inlineEditUpdater;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        InlineEditUpdater $inlineEditor
    ) {
        $this->componentFactory = $componentFactory;
        $this->inlineEditUpdater = $inlineEditor;
    }

    /**
     * @param array $attributeData
     * @param string $columnName
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     * @since 2.0.0
     */
    public function create(array $attributeData, $columnName, $context, array $config = [])
    {
        $config = array_merge([
            'label' => __($attributeData[AttributeMetadata::FRONTEND_LABEL]),
            'dataType' => $this->getDataType($attributeData[AttributeMetadata::FRONTEND_INPUT]),
            'align' => 'left',
            'visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID],
            'component' => $this->getJsComponent($this->getDataType($attributeData[AttributeMetadata::FRONTEND_INPUT])),
        ], $config);
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
     * @param string $dataType
     * @return string
     * @since 2.0.0
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * @param string $frontendType
     * @return string
     * @since 2.0.0
     */
    protected function getDataType($frontendType)
    {
        return isset($this->dataTypeMap[$frontendType])
            ? $this->dataTypeMap[$frontendType]
            : $this->dataTypeMap['default'];
    }
}
