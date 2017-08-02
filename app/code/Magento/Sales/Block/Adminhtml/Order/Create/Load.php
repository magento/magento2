<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Adminhtml sales order create newsletter block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Load extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\View\Helper\Js
     * @since 2.0.0
     */
    protected $_jsHelper = null;

    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     * @since 2.0.0
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\View\Helper\Js $adminhtmlJs
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsHelper = $jsHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $result = [];
        $layout = $this->getLayout();
        foreach ($this->getChildNames() as $name) {
            $result[$name] = $layout->renderElement($name);
        }
        $resultJson = $this->_jsonEncoder->encode($result);
        $jsVarname = $this->getRequest()->getParam('as_js_varname');
        if ($jsVarname) {
            return $this->_jsHelper->getScript(sprintf('var %s = %s', $jsVarname, $resultJson));
        } else {
            return $resultJson;
        }
    }
}
