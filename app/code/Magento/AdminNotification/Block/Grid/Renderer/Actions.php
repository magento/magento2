<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Url\Helper\Data;

/**
 * Class Actions
 *
 * @package Magento\AdminNotification\Block\Grid\Renderer
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Actions extends AbstractRenderer
{
    /**
     * @var Data
     */
    protected $_urlHelper; //phpcs:ignore

    /**
     * @param Context $context
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $urlHelper,
        array $data = []
    ) {
        $this->_urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row): string
    {
        $readDetailsHtml = $row->getUrl() ? '<a class="action-details" target="_blank" href="' . $row->getUrl() . '">' .
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
                    ActionInterface::PARAM_NAME_URL_ENCODED => $encodedUrl
                ]
            ),
            __('Are you sure?'),
            __('Remove')
        );
    }
}
