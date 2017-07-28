<?php
/**
 * Plugin for \Magento\Customer\Model\ResourceModel\Visitor model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin;

/**
 * Class \Magento\Catalog\Model\Plugin\Log
 *
 * @since 2.0.0
 */
class Log
{
    /**
     * @var \Magento\Catalog\Model\Product\Compare\Item
     * @since 2.0.0
     */
    protected $_productCompareItem;

    /**
     * @param \Magento\Catalog\Model\Product\Compare\Item $productCompareItem
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterClean(\Magento\Customer\Model\ResourceModel\Visitor $subject, $logResourceModel)
    {
        $this->_productCompareItem->clean();
        return $logResourceModel;
    }
}
