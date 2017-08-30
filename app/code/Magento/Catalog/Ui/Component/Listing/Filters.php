<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing;

use Magento\Catalog\Ui\Component\FilterFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * @api
 * @since 101.0.0
 */
class Filters implements ObserverInterface
{
    /**
     * @var FilterFactory
     * @since 101.0.0
     */
    protected $filterFactory;

    /**
     * @var CollectionFactory
     * @since 101.0.0
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
     * @since 101.0.0
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
     * @since 101.0.0
     */
    protected function getAttributes($attributeCodes)
    {
        $attributeCollection = $this->attributeCollectionFactory->create();
        return $attributeCollection->addFieldToFilter('attribute_code', ['in' => $attributeCodes]);
    }
}
