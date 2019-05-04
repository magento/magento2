<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\Component\Column\DataTypeConfigProviderPool;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

/**
 * Column Factory
 *
 * @api
 * @since 100.0.2
 */
class ColumnFactory
{
    /**
     * @var UiComponentFactory
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
     * @var DataTypeConfigProviderPool
     */
    private $configProvider;

    /**
     * @param UiComponentFactory $componentFactory
     * @param DataTypeConfigProviderPool $configProvider
     */
    public function __construct(
        UiComponentFactory $componentFactory,
        DataTypeConfigProviderPool $configProvider = null
    ) {
        $this->componentFactory = $componentFactory;
        $this->configProvider = $configProvider
            ?? ObjectManager::getInstance()->get(DataTypeConfigProviderPool::class);
    }

    /**
     * Create Factory
     *
     * @param ProductAttributeInterface $attribute
     * @param ContextInterface $context
     * @param array $config
     *
     * @return ColumnInterface
     * @throws LocalizedException
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
     * @param ProductAttributeInterface $attribute
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
