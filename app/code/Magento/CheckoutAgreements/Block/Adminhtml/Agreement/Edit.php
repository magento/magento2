<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Block\Adminhtml\Agreement;

/**
 * Class \Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init class
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_agreement';
        $this->_blockGroup = 'Magento_CheckoutAgreements';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Condition'));
        $this->buttonList->update('delete', 'label', __('Delete Condition'));
    }

    /**
     * Get Header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkout_agreement')->getId()) {
            return __('Edit Terms and Conditions');
        } else {
            return __('New Terms and Conditions');
        }
    }
}
