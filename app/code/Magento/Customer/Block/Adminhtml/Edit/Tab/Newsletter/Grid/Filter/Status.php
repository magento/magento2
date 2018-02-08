<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter;

use Magento\Newsletter\Model\Queue;

/**
 * Adminhtml newsletter subscribers grid website filter
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            null => null,
            Queue::STATUS_SENT => __('Sent'),
            Queue::STATUS_CANCEL => __('Cancel'),
            Queue::STATUS_NEVER => __('Not Sent'),
            Queue::STATUS_SENDING => __('Sending'),
            Queue::STATUS_PAUSE => __('Paused'),
        ];
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
