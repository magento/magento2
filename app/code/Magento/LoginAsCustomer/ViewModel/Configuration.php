<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\ViewModel;

/**
 * View model to get extension configuration in the template
 */
class Configuration implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Magento\LoginAsCustomer\Model\Config
     */
    private $config;

    /**
     * Configuration constructor.
     * @param \Magento\LoginAsCustomer\Model\Config $config
     */
    public function __construct(
        \Magento\LoginAsCustomer\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve true if login as a customer is enabled
     * @return bool
     */
    public function isEnabled():bool
    {
        return $this->config->isEnabled();
    }
}
