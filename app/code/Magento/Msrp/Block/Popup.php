<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Block;

/**
 * @api
 * @since 2.0.0
 */
class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Msrp\Model\Config $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Msrp\Model\Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return $this->config->isEnabled();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getExplanationMessage()
    {
        return $this->config->getExplanationMessage();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getExplanationMessageWhatsThis()
    {
        return $this->config->getExplanationMessageWhatsThis();
    }
}
