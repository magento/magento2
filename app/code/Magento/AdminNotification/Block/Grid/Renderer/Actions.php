<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Controller\Adminhtml\Notification\MarkAsRead;
use Magento\AdminNotification\Controller\Adminhtml\Notification\Remove;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Url\Helper\Data;

/**
 * Renderer class for action in the admin notifications grid
 */
class Actions extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $urlHelper, array $data = [])
    {
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        $readDetailsHtml = $row->getUrl() ?
            '<a class="action-details" target="_blank" href="' .
            $this->escapeUrl($row->getUrl())
            . '">' .
            __('Read Details') . '</a>' : '';

        $markAsReadHtml = !$row->getIsRead()
            && $this->_authorization->isAllowed(MarkAsRead::ADMIN_RESOURCE) ?
            '<a class="action-mark" href="' . $this->escapeUrl($this->getUrl(
                '*/*/markAsRead/',
                ['_current' => true, 'id' => $row->getNotificationId()]
            )) . '">' . __(
                'Mark as Read'
            ) . '</a>' : '';

        $removeUrl = $this->getUrl(
            '*/*/remove/',
            [
                '_current' => true,
                'id' => $row->getNotificationId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->_urlHelper->getEncodedUrl()
            ]
        );

        $removeHtml = $this->_authorization->isAllowed(Remove::ADMIN_RESOURCE) ?
            '<a class="action-delete" href="'
            . $this->escapeUrl($removeUrl)
            .'" onClick="deleteConfirm('. __('\'Are you sure?\'') .', this.href); return false;">'
            . __('Remove') .  '</a>' : '';

        return sprintf(
            '%s%s%s',
            $readDetailsHtml,
            $markAsReadHtml,
            $removeHtml,
        );
    }
}
