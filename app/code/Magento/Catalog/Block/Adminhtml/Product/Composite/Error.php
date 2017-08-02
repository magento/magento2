<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Composite;

/**
 * Adminhtml block for showing product options fieldsets
 *
 * @api
 * @since 2.0.0
 */
class Error extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Returns error message to show what kind of error happened during retrieving of product
     * configuration controls
     *
     * @return string
     * @since 2.0.0
     */
    public function _toHtml()
    {
        $message = $this->_coreRegistry->registry('composite_configure_result_error_message');
        return $this->_jsonEncoder->encode(['error' => true, 'message' => $message]);
    }
}
