<?php
declare(strict_types=1);

/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Model\Inbox;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Notification\MessageInterface;

/**
 * Renderer class for severity in the admin notifications grid
 */
class Severity extends AbstractRenderer
{
    /**
     * @var \Magento\AdminNotification\Model\Inbox
     */
    protected $_notice;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\AdminNotification\Model\Inbox $notice
     * @param array $data
     */
    public function __construct(Context $context, Inbox $notice, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_notice = $notice;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        $class = '';
        $value = '';

        $column = $this->getColumn();
        $index  = $column->getIndex();
        switch ($row->getData($index)) {
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

        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
