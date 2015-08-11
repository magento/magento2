<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

class Filters extends \Magento\Ui\Component\Filters
{
    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Customer\Ui\Component\FilterFactory $filterFactory
     * @param AttributeRepository $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Customer\Ui\Component\FilterFactory $filterFactory,
        \Magento\Customer\Ui\Component\Listing\AttributeRepository $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->filterFactory = $filterFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($this->attributeRepository->getList() as $attributeCode => $attributeData) {
            if (!isset($this->components[$attributeCode])) {
                if (!$attributeData[AttributeMetadata::BACKEND_TYPE] != 'static'
                    && $attributeData[AttributeMetadata::IS_USED_IN_GRID]
                    && $attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]
                ) {
                    $filter = $this->filterFactory->create($attributeData, $this->getContext());
                    $filter->prepare();
                    $this->addComponent($attributeCode, $filter);
                }
            } elseif ($attributeData[AttributeMetadata::IS_USED_IN_GRID]
                && !$attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]
            ) {
                unset($this->components[$attributeCode]);
            }
        }
        parent::prepare();
    }
}
