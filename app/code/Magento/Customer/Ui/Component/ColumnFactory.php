<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

class ColumnFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'text',
        'select' => 'text',
        'multiselect' => 'text',
        'date' => 'date',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory
    ) {
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param array $attributeData
     * @param string $columnName
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create(array $attributeData, $columnName, $context, array $config = [])
    {
        $config = array_merge([
            'label' => __($attributeData[AttributeMetadata::FRONTEND_LABEL]),
            'dataType' => $this->getDataType($attributeData[AttributeMetadata::FRONTEND_INPUT]),
            'align' => 'left',
            'visible' => (bool)$attributeData[AttributeMetadata::IS_VISIBLE_IN_GRID],
        ], $config);
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
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * @param string $dataType
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * @param string $frontendType
     * @return string
     */
    protected function getDataType($frontendType)
    {
        return isset($this->dataTypeMap[$frontendType])
            ? $this->dataTypeMap[$frontendType]
            : $this->dataTypeMap['default'];
    }
}
