<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Product gallery
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TemplateSelector extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        array $data = []
    ) {
        $this->_setColFactory = $setColFactory;
        $this->_coreRegistry = $registry;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of attribute sets with search part contained in label
     *
     * @param string $labelPart
     * @return array
     */
    public function getSuggestedTemplates($labelPart)
    {
        $product = $this->_coreRegistry->registry('product');
        $entityType = $product->getResource()->getEntityType();
        $labelPart = $this->_resourceHelper->addLikeEscape($labelPart, ['position' => 'any']);
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->_setColFactory->create();
        $collection->setEntityTypeFilter(
            $entityType->getId()
        )->addFieldToFilter(
            'attribute_set_name',
            ['like' => $labelPart]
        )->addFieldToSelect(
            'attribute_set_id',
            'id'
        )->addFieldToSelect(
            'attribute_set_name',
            'label'
        )->setOrder(
            'attribute_set_name',
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
        );
        return $collection->getData();
    }
}
