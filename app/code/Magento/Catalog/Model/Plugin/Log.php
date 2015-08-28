<?php
/**
 * Plugin for \Magento\Customer\Model\Resource\Visitor model
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param \Magento\Customer\Model\Resource\Visitor $subject
     * @param \Magento\Customer\Model\Resource\Visitor $logResourceModel
     *
     * @return \Magento\Customer\Model\Resource\Visitor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterClean(\Magento\Customer\Model\Resource\Visitor $subject, $logResourceModel)
    {
        $this->_productCompareItem->clean();
        return $logResourceModel;
    }
}
