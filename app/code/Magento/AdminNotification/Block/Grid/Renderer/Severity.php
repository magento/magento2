<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Model\Inbox;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Notification\MessageInterface;

/**
 * Class Severity
 *
 * @package Magento\AdminNotification\Block\Grid\Renderer
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Severity extends AbstractRenderer
{
    /**
     * @var Inbox
     */
    protected $_notice; //phpcs:ignore

    /**
     * @param Context $context
     * @param Inbox $notice
     * @param array $data
     */
    public function __construct(
        Context $context,
        Inbox $notice,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_notice = $notice;
    }

    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row): string
    {
        $class = '';
        $value = '';

        switch ($row->getData($this->getColumn()->getIndex())) {
            case MessageInterface::SEVERITY_CRITICAL:
                $class = 'critical';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_CRITICAL);
                break;
            case MessageInterface::SEVERITY_MAJOR:
                $class = 'major';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_MAJOR);
                break;
            case MessageInterface::SEVERITY_MINOR:
                $class = 'minor';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_MINOR);
                break;
            case MessageInterface::SEVERITY_NOTICE:
                $class = 'notice';
                $value = $this->_notice->getSeverities(MessageInterface::SEVERITY_NOTICE);
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . (int)$value . '</span></span>';
    }
}
