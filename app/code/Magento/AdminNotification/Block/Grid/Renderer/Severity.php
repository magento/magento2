<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

use Magento\Framework\Notification\MessageInterface;

/**
 * Class \Magento\AdminNotification\Block\Grid\Renderer\Severity
 *
 */
class Severity extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\AdminNotification\Model\Inbox $notice,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_notice = $notice;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
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
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
