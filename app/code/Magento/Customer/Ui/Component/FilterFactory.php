<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

/**
 * Class \Magento\Customer\Ui\Component\FilterFactory
 *
 * @since 2.0.0
 */
class FilterFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     * @since 2.0.0
     */
    protected $componentFactory;

    /**
     * @var array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\View\Element\UiComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * @param array $attributeData
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     * @since 2.0.0
     */
    public function create(array $attributeData, $context)
    {
        $config = [
            'dataScope' => $attributeData[AttributeMetadata::ATTRIBUTE_CODE],
            'label' => __($attributeData[AttributeMetadata::FRONTEND_LABEL]),
        ];
        if ($attributeData[AttributeMetadata::OPTIONS]) {
            $config['options'] = $attributeData[AttributeMetadata::OPTIONS];
            $config['caption'] = __('Select...');
        }
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create(
            $attributeData[AttributeMetadata::ATTRIBUTE_CODE],
            $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]),
            $arguments
        );
    }

    /**
     * @param string $frontendInput
     * @return string
     * @since 2.0.0
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
