<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Config category source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
 */
class Category implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     * @since 2.0.0
     */
    protected $_categoryCollectionFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray($addEmpty = true)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->_categoryCollectionFactory->create();

        $collection->addAttributeToSelect('name')->addRootLevelFilter()->load();

        $options = [];

        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }

        return $options;
    }
}
