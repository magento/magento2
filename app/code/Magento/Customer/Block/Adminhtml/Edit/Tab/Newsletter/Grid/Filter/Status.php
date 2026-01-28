<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

use Magento\Newsletter\Model\Queue;

/**
 * Adminhtml newsletter subscribers grid website filter
 */
class Status extends Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * Initialize available newsletter statuses.
     *
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            '' => null,
            Queue::STATUS_SENT => __('Sent'),
            Queue::STATUS_CANCEL => __('Cancel'),
            Queue::STATUS_NEVER => __('Not Sent'),
            Queue::STATUS_SENDING => __('Sending'),
            Queue::STATUS_PAUSE => __('Paused'),
        ];
        parent::_construct();
    }

    /**
     * Build select options for newsletter statuses.
     *
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
     * Get filter condition from the selected status value.
     *
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
