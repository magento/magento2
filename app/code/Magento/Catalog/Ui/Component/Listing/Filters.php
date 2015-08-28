<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing;

class Filters extends \Magento\Ui\Component\Filters
{
    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\FilterFactory $filterFactory
     * @param AttributeRepository $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\FilterFactory $filterFactory,
        \Magento\Catalog\Ui\Component\Listing\AttributeRepository $attributeRepository,
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
        foreach ($this->attributeRepository->getList() as $attribute) {
            if (!isset($this->components[$attribute->getAttributeCode()]) && $attribute->getIsFilterableInGrid()) {
                $filter = $this->filterFactory->create($attribute, $this->getContext());
                $filter->prepare();
                $this->addComponent($attribute->getAttributeCode(), $filter);
            }
        }
        parent::prepare();
    }
}
