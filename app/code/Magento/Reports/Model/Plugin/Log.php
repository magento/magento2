<?php
/**
 * Plugin for \Magento\Customer\Model\ResourceModel\Visitor model
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\Plugin;

class Log
{
    /**
     * @var \Magento\Reports\Model\Event
     */
    protected $_reportEvent;

    /**
     * @var \Magento\Reports\Model\Product\Index\Compared
     */
    protected $_comparedProductIdx;

    /**
     * @var \Magento\Reports\Model\Product\Index\Viewed
     */
    protected $_viewedProductIdx;

    /**
     * @param \Magento\Reports\Model\Event $reportEvent
     * @param \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx
     * @param \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
     */
    public function __construct(
        \Magento\Reports\Model\Event $reportEvent,
        \Magento\Reports\Model\Product\Index\Compared $comparedProductIdx,
        \Magento\Reports\Model\Product\Index\Viewed $viewedProductIdx
    ) {
        $this->_reportEvent = $reportEvent;
        $this->_comparedProductIdx = $comparedProductIdx;
        $this->_viewedProductIdx = $viewedProductIdx;
    }

    /**
     * Clean events by old visitors after plugin for clean method
     *
     * @param \Magento\Customer\Model\ResourceModel\Visitor $subject
     * @param \Magento\Customer\Model\ResourceModel\Visitor $logResourceModel
     *
     * @return \Magento\Customer\Model\ResourceModel\Visitor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see Global Log Clean Settings
     */
    public function afterClean(\Magento\Customer\Model\ResourceModel\Visitor $subject, $logResourceModel)
    {
        $this->_reportEvent->clean();
        $this->_comparedProductIdx->clean();
        $this->_viewedProductIdx->clean();
        return $logResourceModel;
    }
}
