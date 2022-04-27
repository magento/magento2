<?php
/**
 * Google AdWords Code block
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Helper\GtagConfiguration;

/**
 * Google Ads Code block
 *
 * @api
 * @since 100.0.2
 */
class Code extends Template
{
    /**
     * @var GtagConfiguration
     */
    private $googleGtagConfig;

    /**
     * @param Context $context
     * @param GtagConfiguration $googleGtagConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        GtagConfiguration $googleGtagConfig,
        array $data = []
    ) {
        $this->googleGtagConfig = $googleGtagConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render block html if Google AdWords is active
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        return $this->googleGtagConfig->isGoogleAdwordsActive() ? parent::_toHtml() : '';
    }

    /**
     * Return helper
     *
     * @return GtagConfiguration
     */
    public function getHelper(): GtagConfiguration
    {
        return $this->googleGtagConfig;
    }
}
