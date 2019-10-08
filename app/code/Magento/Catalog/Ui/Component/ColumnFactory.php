<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

use Magento\Ui\Component\Filters\FilterModifier;

/**
 * Column Factory
 *
 * @api
 * @since 100.0.2
 */
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
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
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
        'multiselect' => 'multiselect',
        'date' => 'date',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     */
    public function __construct(\Magento\Framework\View\Element\UiComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * Create Factory
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config
     *
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($attribute, $context, array $config = [])
    {
        $filterModifiers = $context->getRequestParam(FilterModifier::FILTER_MODIFIER, []);

        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->getDataType($attribute),
            'add_field' => true,
            'visible' => $attribute->getIsVisibleInGrid(),
            'filter' => ($attribute->getIsFilterableInGrid() || array_key_exists($columnName, $filterModifiers))
                ? $this->getFilterType($attribute->getFrontendInput())
                : null,
            '__disableTmpl' => ['label' => true],
        ], $config);

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
            foreach ($config['options'] as &$optionData) {
                $optionData['__disableTmpl'] = true;
            }
        }
        
        $config['component'] = $this->getJsComponent($config['dataType']);
        
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];
        
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * Get Js Component
     *
     * @param string $dataType
     *
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * Get Data Type
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     *
     * @return string
     */
    protected function getDataType($attribute)
    {
        return $this->dataTypeMap[$attribute->getFrontendInput()] ?? $this->dataTypeMap['default'];
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        $filtersMap = ['date' => 'dateRange'];
        $result = array_replace_recursive($this->dataTypeMap, $filtersMap);
        return $result[$frontendInput] ?? $result['default'];
    }
}
