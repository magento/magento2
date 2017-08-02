<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Block form after
 */
namespace Magento\ImportExport\Block\Adminhtml\Form;

/**
 * @api
 * @since 2.0.0
 */
class After extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_registry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current operation
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function getOperation()
    {
        return $this->_registry->registry('current_operation');
    }
}
