<?php
/**
 * Plugin for \Magento\Log\Model\Resource\Log model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @param \Magento\Log\Model\Resource\Log $subject
     * @param \Magento\Log\Model\Resource\Log $logResourceModel
     *
     * @return \Magento\Log\Model\Resource\Log
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterClean(\Magento\Log\Model\Resource\Log $subject, $logResourceModel)
    {
        $this->_productCompareItem->clean();
        return $logResourceModel;
    }
}
