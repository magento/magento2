<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Captcha block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Block;

/**
 * @api
 * @since 2.0.0
 */
class Captcha extends \Magento\Framework\View\Element\Template
{
    /**
     * Captcha data
     *
     * @var \Magento\Captcha\Helper\Data
     * @since 2.0.0
     */
    protected $_captchaData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Captcha\Helper\Data $captchaData,
        array $data = []
    ) {
        $this->_captchaData = $captchaData;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Renders captcha HTML (if required)
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $blockPath = $this->_captchaData->getCaptcha($this->getFormId())->getBlockName();
        $block = $this->getLayout()->createBlock($blockPath);
        $block->setData($this->getData());
        return $block->toHtml();
    }
}
