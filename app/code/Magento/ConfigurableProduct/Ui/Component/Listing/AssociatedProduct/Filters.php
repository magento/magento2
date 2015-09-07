<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct;

class Filters extends \Magento\Ui\Component\Filters
{
    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\FilterFactory $filterFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\FilterFactory $filterFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->filterFactory = $filterFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $attributeIds = $this->context->getRequestParam('attribute_ids');
        if ($attributeIds) {
            foreach ($this->getAttributes($attributeIds) as $attribute) {
                $filter = $this->filterFactory->create($attribute, $this->getContext());
                $filter->prepare();
                $this->addComponent($attribute->getAttributeCode(), $filter);
            }
        }
        parent::prepare();
    }

    /**
     * @param array $attributeIds
     * @return mixed
     */
    protected function getAttributes($attributeIds)
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        return $attributeCollection->addFieldToFilter('attribute_code', ['in' => $attributeIds]);
    }
}
