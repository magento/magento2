<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component;

use \Magento\Framework\Exception\LocalizedException;

class ColumnFactory
{
    /**
     * Column class name
     */
    const COLUMN_CLASS = 'Magento\Ui\Component\Listing\Columns\Column';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/sortable',
        'select' => 'Magento_Ui/js/grid/columns/select',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     */
    public function create($attribute, $context)
    {
        $columnName = $attribute->getAttributeCode();
        $config = [
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => 'text',
            'sorting' => 'asc',
            'align' => 'left',
            'add_field' => true,
        ];
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions();
            $config['dataType'] = 'select';
            $config['options'] = $options;
        }
        $arguments = [
            'data' => [
                'state_prefix' => 'columns',
                'js_config' => [
                    'component' => $this->jsComponentMap[$config['dataType']],
                ],
                'config' => $config,
                'name' => $columnName
            ],
            'components' => [],
            'context' => $context,
        ];
        return $this->objectManager->create(self::COLUMN_CLASS, $arguments);
    }
}
