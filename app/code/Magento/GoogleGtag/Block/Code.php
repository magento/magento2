<?php
/**
 * Google AdWords Code block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Helper\Data;

/**
 * Google Ads Code block
 *
 * @api
 * @since 100.0.2
 */
class Code extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $_googleGtagData;

    /**
     * @param Context $context
     * @param Data $googleGtagData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $googleGtagData,
        array $data = []
    ) {
        $this->_googleGtagData = $googleGtagData;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google AdWords is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_googleGtagData->isGoogleAdwordsActive() ? parent::_toHtml() : '';
    }

    /**
     * Return helper
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->_googleGtagData;
    }
}
