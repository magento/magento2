<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

/**
 * @api
 * @since 100.0.2
 */
class FilterFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'filterInput',
        'select' => 'filterSelect',
        'boolean' => 'filterSelect',
        'multiselect' => 'filterSelect',
        'date' => 'filterDate',
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
     * @param array $config
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create($attribute, $context, $config = [])
    {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge(
            [
                'dataScope' => $columnName,
                'label' => __($attribute->getDefaultFrontendLabel()),
            ],
            $config
        );
        if ($attribute->usesSource() && $attribute->getSourceModel()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
            $config['caption'] = __('Select...');
        }
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, $this->getFilterType($attribute), $arguments);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string
     */
    protected function getFilterType($attribute)
    {
        return isset($this->filterMap[$attribute->getFrontendInput()])
            ? $this->filterMap[$attribute->getFrontendInput()]
            : $this->filterMap['default'];
    }
}
