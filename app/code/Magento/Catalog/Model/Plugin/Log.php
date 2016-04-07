<?php
/**
 * Plugin for \Magento\Customer\Model\ResourceModel\Visitor model
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

class Log
{
    /**
     * @var \Magento\Catalog\Model\Product\Compare\Item
     */
    protected $_productCompareItem;

    /**
     * @param \Magento\Catalog\Model\Product\Compare\Item $productCompareItem
     */
    public function __construct(\Magento\Catalog\Model\Product\Compare\Item $productCompareItem)
    {
        $this->_productCompareItem = $productCompareItem;
    }

    /**
     * Catalog Product Compare Items Clean
     * after plugin for clean method
     *
     * @param \Magento\Customer\Model\ResourceModel\Visitor $subject
     * @param \Magento\Customer\Model\ResourceModel\Visitor $logResourceModel
     *
     * @return \Magento\Customer\Model\ResourceModel\Visitor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterClean(\Magento\Customer\Model\ResourceModel\Visitor $subject, $logResourceModel)
    {
        $this->_productCompareItem->clean();
        return $logResourceModel;
    }
}
