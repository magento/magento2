<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Renderer;

/**
 * Adminhtml newsletter queue grid block status item renderer
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * Constructor for Grid Renderer Status
     *
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            \Magento\Newsletter\Model\Queue::STATUS_SENT => __('Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_CANCEL => __('Cancel'),
            \Magento\Newsletter\Model\Queue::STATUS_NEVER => __('Not Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_SENDING => __('Sending'),
            \Magento\Newsletter\Model\Queue::STATUS_PAUSE => __('Paused'),
        ];
        parent::_construct();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return __($this->getStatus($row->getQueueStatus()));
    }

    /**
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public static function getStatus($status)
    {
        if (isset(self::$_statuses[$status])) {
            return self::$_statuses[$status];
        }

        return __('Unknown');
    }
}
