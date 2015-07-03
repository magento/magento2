<?php
/**
 * List of suggested attributes
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

class SuggestedAttributeList
{
    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $resourceHelper;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Object Factory
     *
     * @var \Magento\Framework\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\ObjectFactory $objectFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\ObjectFactory $objectFactory
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->resourceHelper = $resourceHelper;
        $this->objectFactory = $objectFactory;
        $this->eventManager = $eventManager;
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getSuggestedAttributes($labelPart)
    {
        $escapedLabelPart = $this->resourceHelper->addLikeEscape($labelPart, ['position' => 'any']);
        $availableFrontendTypes = $this->getAvailableFrontendTypes();

        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            'main_table.frontend_input',
            ['in' => $availableFrontendTypes->getData('values')]
        )->addFieldToFilter(
            'frontend_label',
            ['like' => $escapedLabelPart]
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL
        );

        $result = [];
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];
        foreach ($collection->getItems() as $id => $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if (!$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0) {
                $result[$id] = [
                    'id' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'options' => $attribute->getSource()->getAllOptions(false),
                ];
            }
        }
        return $result;
    }

    /**
     * @return \Magento\Framework\Object
     */
    private function getAvailableFrontendTypes()
    {
        $availableFrontendTypes = $this->objectFactory->create();
        $availableFrontendTypes->setData(
            [
                'values' => ['select']
            ]
        );
        $this->eventManager->dispatch(
            'product_suggested_attribute_frontend_type_init_after',
            ['types_dto' => $availableFrontendTypes]
        );
        return $availableFrontendTypes;
    }
}
