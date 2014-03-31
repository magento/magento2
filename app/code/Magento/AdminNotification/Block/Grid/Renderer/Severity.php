<?php
/**
 * Adminhtml AdminNotification Severity Renderer
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_AdminNotification
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Block\Grid\Renderer;

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
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_notice = $notice;
    }

    /**
     * Renders grid column
     *
     * @param   \Magento\Object $row
     * @return  string
     */
    public function render(\Magento\Object $row)
    {
        $class = '';
        $value = '';

        switch ($row->getData($this->getColumn()->getIndex())) {
            case \Magento\AdminNotification\Model\Inbox::SEVERITY_CRITICAL:
                $class = 'critical';
                $value = $this->_notice->getSeverities(\Magento\AdminNotification\Model\Inbox::SEVERITY_CRITICAL);
                break;
            case \Magento\AdminNotification\Model\Inbox::SEVERITY_MAJOR:
                $class = 'major';
                $value = $this->_notice->getSeverities(\Magento\AdminNotification\Model\Inbox::SEVERITY_MAJOR);
                break;
            case \Magento\AdminNotification\Model\Inbox::SEVERITY_MINOR:
                $class = 'minor';
                $value = $this->_notice->getSeverities(\Magento\AdminNotification\Model\Inbox::SEVERITY_MINOR);
                break;
            case \Magento\AdminNotification\Model\Inbox::SEVERITY_NOTICE:
                $class = 'notice';
                $value = $this->_notice->getSeverities(\Magento\AdminNotification\Model\Inbox::SEVERITY_NOTICE);
                break;
        }
        return '<span class="grid-severity-' . $class . '"><span>' . $value . '</span></span>';
    }
}
