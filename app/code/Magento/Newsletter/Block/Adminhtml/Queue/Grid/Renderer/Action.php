<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter queue grid block action item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block\Adminhtml\Queue\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * Renders column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = [];

        if ($row->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_NEVER) {
            if (!$row->getQueueStartAt() && $row->getSubscribersTotal()) {
                $actions[] = [
                    'url' => $this->getUrl('*/*/start', ['id' => $row->getId()]),
                    'caption' => __('Start'),
                ];
            }
        } elseif ($row->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_SENDING) {
            $actions[] = [
                'url' => $this->getUrl('*/*/pause', ['id' => $row->getId()]),
                'caption' => __('Pause'),
            ];

            $actions[] = [
                'url' => $this->getUrl('*/*/cancel', ['id' => $row->getId()]),
                'confirm' => __('Do you really want to cancel the queue?'),
                'caption' => __('Cancel'),
            ];
        } elseif ($row->getQueueStatus() == \Magento\Newsletter\Model\Queue::STATUS_PAUSE) {
            $actions[] = [
                'url' => $this->getUrl('*/*/resume', ['id' => $row->getId()]),
                'caption' => __('Resume'),
            ];
        }

        $actions[] = [
            'url' => $this->getUrl('*/*/preview', ['id' => $row->getId()]),
            'caption' => __('Preview'),
            'popup' => true,
        ];

        $this->getColumn()->setActions($actions);
        return parent::render($row);
    }
}
