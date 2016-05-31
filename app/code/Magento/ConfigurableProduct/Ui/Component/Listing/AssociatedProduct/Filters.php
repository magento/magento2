<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct;

use Magento\Catalog\Ui\Component\FilterFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentInterface;

class Filters implements ObserverInterface
{
    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @param FilterFactory $filterFactory
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        FilterFactory $filterFactory,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->filterFactory = $filterFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function update(UiComponentInterface $component)
    {
        if (!$component instanceof \Magento\Ui\Component\Filters) {
            return;
        }

        $attributeCodes = $component->getContext()->getRequestParam('attributes_codes');
        if ($attributeCodes) {
            foreach ($this->getAttributes($attributeCodes) as $attribute) {
                $filter = $this->filterFactory->create($attribute, $component->getContext());
                $filter->prepare();
                $component->addComponent($attribute->getAttributeCode(), $filter);
            }
        }
    }

    /**
     * @param array $attributeCodes
     * @return mixed
     */
    protected function getAttributes($attributeCodes)
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        return $attributeCollection->addFieldToFilter('attribute_code', ['in' => $attributeCodes]);
    }
}
