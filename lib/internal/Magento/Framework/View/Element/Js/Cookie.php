<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Cookie extends Template
{
    /**
     * Session config
     *
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
        $domain = $this->sessionConfig->getCookieDomain();

        if ($this->ipValidator->isValid($domain)) {
            return $domain;
        }

        if (!empty($domain[0]) && $domain[0] !== '.') {
            $domain = '.' . $domain;
        }
        return $domain;
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
}
