<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing;

class Columns extends  \Magento\Ui\Component\Listing\Columns
{
    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\Collection $attributeCollection,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeCollection = $attributeCollection;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->attributeCollection->addIsUsedInGridFilter();
        foreach ($this->attributeCollection as $attribute) {
            $attribute = $this->productAttributeRepository->get($attribute->getAttributeId());
            $column = $this->columnFactory->create($attribute, $this->getContext());
            $column->prepare();
            $this->addComponent($attribute->getAttributeCode(), $column);
        }
        parent::prepare();
    }
}
