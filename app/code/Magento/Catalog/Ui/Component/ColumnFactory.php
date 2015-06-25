<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

class ColumnFactory
{
    /**
     * @var int
     */
    private $columnSortOrder = 0;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/sortable',
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
     */
    public function __construct(\Magento\Framework\View\Element\UiComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param int $columnsAmount
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create($attribute, $context, $columnsAmount)
    {
        $this->columnSortOrder += 1;
        $columnName = $attribute->getAttributeCode();
        $config = [
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->getDataType($attribute),
            'sorting' => 'asc',
            'align' => 'left',
            'add_field' => true,
            'visible' => $attribute->getIsVisibleInGrid(),
            'sortOrder' => $this->columnSortOrder + $columnsAmount,
        ];

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
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
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string
     */
    protected function getDataType($attribute)
    {
        return isset($this->dataTypeMap[$attribute->getFrontendInput()])
            ? $this->dataTypeMap[$attribute->getFrontendInput()]
            : $this->dataTypeMap['default'];
    }
}
