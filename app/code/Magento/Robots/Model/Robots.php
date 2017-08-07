<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns data for robots.txt file
 * @since 2.2.0
 */
class Robots
{
    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get the main data for robots.txt file as defined in configuration
     *
     * @return string
     * @since 2.2.0
     */
    public function getData()
    {
        return $this->scopeConfig->getValue(
            'design/search_engine_robots/custom_instructions',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
