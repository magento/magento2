<?php
/**
 * Google AdWords Code block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Block;

/**
 * @api
 * @since 2.0.0
 */
class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     * @since 2.0.0
     */
    protected $_googleAdwordsData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\GoogleAdwords\Helper\Data $googleAdwordsData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GoogleAdwords\Helper\Data $googleAdwordsData,
        array $data = []
    ) {
        $this->_googleAdwordsData = $googleAdwordsData;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google AdWords is active
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return $this->_googleAdwordsData->isGoogleAdwordsActive() ? parent::_toHtml() : '';
    }

    /**
     * @return \Magento\GoogleAdwords\Helper\Data
     * @since 2.0.0
     */
    public function getHelper()
    {
        return $this->_googleAdwordsData;
    }
}
