<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Helper\Data;

/**
 * Google Ads Head block
 *
 * @api
 */
class Head extends Template
{
    /**
     * @var Data
     */
    protected $googleGtagData;

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
        $this->googleGtagData = $googleGtagData;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google AdWords is active
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->googleGtagData->isGoogleAdwordsActive() ? parent::_toHtml() : '';
    }

    /**
     * Return helper
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->googleGtagData;
    }
}
