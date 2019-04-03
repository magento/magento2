<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

use Magento\Catalog\Ui\Component\Column\DataTypeConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
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
     * @var \Magento\Catalog\Ui\Component\Column\DataTypeConfigProviderInterface
     */
    private $configProvider;

    /**
     * @param \Magento\Framework\View\Element\UiComponentFactory                   $componentFactory
     * @param \Magento\Catalog\Ui\Component\Column\DataTypeConfigProviderInterface $configProvider
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        DataTypeConfigProviderInterface $configProvider = null
    ) {
        $this->componentFactory = $componentFactory;
        $this->configProvider = $configProvider
            ?? ObjectManager::getInstance()->get(DataTypeConfigProviderInterface::class);
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
        ], $config);

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }
        
        $config['component'] = $this->getJsComponent($config['dataType']);
        
        $arguments = [
            'data' => [
                'config' => $config + $this->configProvider->getConfig($config['dataType']),
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
