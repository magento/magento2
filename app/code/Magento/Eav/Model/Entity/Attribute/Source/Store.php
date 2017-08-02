<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

/**
 * Customer store_id attribute source
 *
 * @api
 * @since 2.0.0
 */
class Store extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     * @since 2.0.0
     */
    protected $_storeCollectionFactory;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->_storeCollectionFactory = $storeCollectionFactory;
    }

    /**
     * Retrieve Full Option values array
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->_storeCollectionFactory->create()->load()->toOptionArray();
        }
        return $this->_options;
    }
}
