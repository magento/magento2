<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block passes configuration for cookies set by JS
 *
 * @api
 * @since 100.0.2
 */
class Cookie extends Template
{
    /**
     * @var ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Magento\Framework\Validator\Ip
     */
    protected $ipValidator;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigInterface $cookieConfig
     * @param \Magento\Framework\Validator\Ip $ipValidator
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $cookieConfig,
        \Magento\Framework\Validator\Ip $ipValidator,
        array $data = []
    ) {
        $this->sessionConfig = $cookieConfig;
        $this->ipValidator = $ipValidator;
        parent::__construct($context, $data);
    }

    /**
     * Get configured cookie domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->sessionConfig->getCookieDomain();
    }

    /**
     * Get configured cookie path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->sessionConfig->getCookiePath();
    }

    /**
     * Get configured cookie lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->sessionConfig->getCookieLifetime();
    }
}
