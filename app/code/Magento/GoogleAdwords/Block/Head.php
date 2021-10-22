<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\GoogleAdwords\Block;

class Head extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\GoogleAdwords\Helper\Data
     */
    protected $googleAdwordsData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\GoogleAdwords\Helper\Data $googleAdwordsData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\GoogleAdwords\Helper\Data $googleAdwordsData,
        array $data = []
    ) {
        $this->googleAdwordsData = $googleAdwordsData;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google AdWords is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->googleAdwordsData->isGoogleAdwordsActive() ? parent::_toHtml() : '';
    }

    /**
     * @return \Magento\GoogleAdwords\Helper\Data
     */
    public function getHelper()
    {
        return $this->googleAdwordsData;
    }
}
