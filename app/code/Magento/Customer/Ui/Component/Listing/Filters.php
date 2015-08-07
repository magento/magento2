<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing;

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
        /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
        foreach ($this->attributeRepository->getList() as $attributeCode => $attribute) {
            if (!isset($this->components[$attributeCode])) {
                if ($attribute->getBackendType() !== 'static'
                    && $attribute->getIsUsedInGrid()
                    && $attribute->getIsFilterableInGrid()
                ) {
                    $filter = $this->filterFactory->create($attributeCode, $attribute, $this->getContext());
                    $filter->prepare();
                    $this->addComponent($attribute->getAttributeCode(), $filter);
                }
            } elseif ($attribute->getIsUsedInGrid() && !$attribute->getIsFilterableInGrid()) {
                unset($this->components[$attributeCode]);
            }
        }
        parent::prepare();
    }

}
