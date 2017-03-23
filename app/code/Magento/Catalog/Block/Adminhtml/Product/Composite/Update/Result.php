<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml block for result of catalog product composite update
 * Forms response for a popup window for a case when form is directly submitted
 * for single item
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Composite\Update;

class Result extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Helper\Js
     */
    protected $_jsHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsHelper = $jsHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Forms script response
     *
     * @return string
     */
    public function _toHtml()
    {
        $updateResult = $this->_coreRegistry->registry('composite_update_result');
        $resultJson = $this->_jsonEncoder->encode($updateResult);
        $jsVarname = $updateResult->getJsVarName();
        return $this->_jsHelper->getScript(sprintf('var %s = %s', $jsVarname, $resultJson));
    }
}
