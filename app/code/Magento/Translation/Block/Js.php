<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\Js\Config;

class Js extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
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
     * Is js translation set to dictionary mode
     *
     * @return bool
     */
    public function dictionaryEnabled()
    {
        return $this->config->dictionaryEnabled();
    }
}
