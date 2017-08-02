<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block;

use Magento\Framework\View\Element\Template;
use Magento\Ui\Model\Config;

/**
 * Logger block
 *
 * @api
 * @since 2.0.0
 */
class Logger extends Template
{
    /**
     * @var Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Is session storage logging enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isLoggingEnabled()
    {
        return $this->config->isLoggingEnabled();
    }

    /**
     * Get session storage key
     *
     * @return string
     * @since 2.0.0
     */
    public function getSessionStorageKey()
    {
        return $this->config->getSessionStorageKey();
    }
}
