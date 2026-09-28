<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Robots\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns data for robots.txt file
 */
class Robots
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
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
     */
    public function getData()
    {
        return $this->scopeConfig->getValue(
            'design/search_engine_robots/custom_instructions',
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
