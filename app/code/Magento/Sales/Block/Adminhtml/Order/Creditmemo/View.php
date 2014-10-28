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
namespace Magento\Sales\Block\Adminhtml\Order\Creditmemo;

/**
 * Adminhtml creditmemo view
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class View extends \Magento\Backend\Block\Widget\Form\Container
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
     * Add & remove control buttons
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'creditmemo_id';
        $this->_controller = 'adminhtml_order_creditmemo';
        $this->_mode = 'view';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        if (!$this->getCreditmemo()) {
            return;
        }

        if ($this->getCreditmemo()->canCancel()) {
            $this->buttonList->add(
                'cancel',
                array(
                    'label' => __('Cancel'),
                    'class' => 'delete',
                    'onclick' => 'setLocation(\'' . $this->getCancelUrl() . '\')'
                )
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails')) {
            $this->addButton(
                'send_notification',
                array(
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure you want to send a Credit memo email to customer?'
                    ) . '\', \'' . $this->getEmailUrl() . '\')'
                )
            );
        }

        if ($this->getCreditmemo()->canRefund()) {
            $this->buttonList->add(
                'refund',
                array(
                    'label' => __('Refund'),
                    'class' => 'refund',
                    'onclick' => 'setLocation(\'' . $this->getRefundUrl() . '\')'
                )
            );
        }

        if ($this->getCreditmemo()->canVoid()) {
            $this->buttonList->add(
                'void',
                array(
                    'label' => __('Void'),
                    'class' => 'void',
                    'onclick' => 'setLocation(\'' . $this->getVoidUrl() . '\')'
                )
            );
        }

        if ($this->getCreditmemo()->getId()) {
            $this->buttonList->add(
                'print',
                array(
                    'label' => __('Print'),
                    'class' => 'print',
                    'onclick' => 'setLocation(\'' . $this->getPrintUrl() . '\')'
                )
            );
        }
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * Retrieve text for header
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getCreditmemo()->getEmailSent()) {
            $emailSent = __('The credit memo email was sent');
        } else {
            $emailSent = __('the credit memo email is not sent');
        }
        return __(
            'Credit Memo #%1 | %3 | %2 (%4)',
            $this->getCreditmemo()->getIncrementId(),
            $this->formatDate($this->getCreditmemo()->getCreatedAtDate(), 'medium', true),
            $this->getCreditmemo()->getStateName(),
            $emailSent
        );
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            array(
                'order_id' => $this->getCreditmemo() ? $this->getCreditmemo()->getOrderId() : null,
                'active_tab' => 'order_creditmemos'
            )
        );
    }

    /**
     * Retrieve capture url
     *
     * @return string
     */
    public function getCaptureUrl()
    {
        return $this->getUrl('sales/*/capture', array('creditmemo_id' => $this->getCreditmemo()->getId()));
    }

    /**
     * Retrieve void url
     *
     * @return string
     */
    public function getVoidUrl()
    {
        return $this->getUrl('sales/*/void', array('creditmemo_id' => $this->getCreditmemo()->getId()));
    }

    /**
     * Retrieve cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('sales/*/cancel', array('creditmemo_id' => $this->getCreditmemo()->getId()));
    }

    /**
     * Retrieve email url
     *
     * @return string
     */
    public function getEmailUrl()
    {
        return $this->getUrl(
            'sales/*/email',
            array(
                'creditmemo_id' => $this->getCreditmemo()->getId(),
                'order_id' => $this->getCreditmemo()->getOrderId()
            )
        );
    }

    /**
     * Retrieve print url
     *
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('sales/*/print', array('creditmemo_id' => $this->getCreditmemo()->getId()));
    }

    /**
     * Update 'back' button url
     *
     * @param bool $flag
     * @return \Magento\Backend\Block\Widget\Container|$this
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getCreditmemo()->getBackUrl()) {
                return $this->buttonList->update(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getCreditmemo()->getBackUrl() . '\')'
                );
            }

            return $this->buttonList->update(
                'back',
                'onclick',
                'setLocation(\'' . $this->getUrl('sales/creditmemo/') . '\')'
            );
        }
        return $this;
    }

    /**
     * Check whether action is allowed
     *
     * @param string $resourceId
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
