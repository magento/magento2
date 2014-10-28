<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring payment view page
 */
namespace Magento\RecurringPayment\Block\Adminhtml\Payment;

class View extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Create buttons
     * TODO: implement ACL restrictions
     * @return \Magento\RecurringPayment\Block\Adminhtml\Payment\View
     */
    protected function _prepareLayout()
    {
        $this->buttonList->add(
            'back',
            array('label' => __('Back'), 'onclick' => "setLocation('{$this->getUrl('*/*/')}')", 'class' => 'back')
        );

        $payment = $this->_coreRegistry->registry('current_recurring_payment');
        $confirmationMessage = __('Are you sure you want to do this?');

        // cancel
        if ($payment->canCancel()) {
            $url = $this->getUrl('*/*/updateState', array('payment' => $payment->getId(), 'action' => 'cancel'));
            $this->buttonList->add(
                'cancel',
                array(
                    'label' => __('Cancel'),
                    'onclick' => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                    'class' => 'delete'
                )
            );
        }

        // suspend
        if ($payment->canSuspend()) {
            $url = $this->getUrl('*/*/updateState', array('payment' => $payment->getId(), 'action' => 'suspend'));
            $this->buttonList->add(
                'suspend',
                array(
                    'label' => __('Suspend'),
                    'onclick' => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                    'class' => 'delete'
                )
            );
        }

        // activate
        if ($payment->canActivate()) {
            $url = $this->getUrl('*/*/updateState', array('payment' => $payment->getId(), 'action' => 'activate'));
            $this->buttonList->add(
                'activate',
                array(
                    'label' => __('Activate'),
                    'onclick' => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                    'class' => 'add'
                )
            );
        }

        // get update
        if ($payment->canFetchUpdate()) {
            $url = $this->getUrl('*/*/updatePayment', array('payment' => $payment->getId()));
            $this->buttonList->add(
                'update',
                array(
                    'label' => __('Get Update'),
                    'onclick' => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                    'class' => 'add'
                )
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Set title and a hack for tabs container
     *
     * @return \Magento\RecurringPayment\Block\Adminhtml\Payment\View
     */
    protected function _beforeToHtml()
    {
        $payment = $this->_coreRegistry->registry('current_recurring_payment');
        $this->_headerText = __('Recurring Payment # %1', $payment->getReferenceId());
        $this->setViewHtml('<div id="' . $this->getDestElementId() . '"></div>');
        return parent::_beforeToHtml();
    }
}
