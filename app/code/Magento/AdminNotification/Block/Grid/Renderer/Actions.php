<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Block\Grid\Renderer;

/**
 * Renderer class for action in the admin notifications grid
 *
 * @package Magento\AdminNotification\Block\Grid\Renderer
 */
class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $readDetailsHtml = $row->getUrl() ? '<a class="action-details" target="_blank" href="' .
            $this->escapeUrl($row->getUrl())
            . '">' .
            __('Read Details') . '</a>' : '';

        $markAsReadHtml = !$row->getIsRead() ? '<a class="action-mark" href="' . $this->getUrl(
            '*/*/markAsRead/',
            ['_current' => true, 'id' => $row->getId()]
        ) . '">' . __(
            'Mark as Read'
        ) . '</a>' : '';

        $encodedUrl = $this->_urlHelper->getEncodedUrl();
        return sprintf(
            '%s%s<a class="action-delete" href="%s" onClick="deleteConfirm(\'%s\', this.href); return false;">%s</a>',
            $readDetailsHtml,
            $markAsReadHtml,
            $this->getUrl(
                '*/*/remove/',
                [
                    '_current' => true,
                    'id' => $row->getId(),
                    \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl
                ]
            ),
            __('Are you sure?'),
            __('Remove')
        );
    }
}
