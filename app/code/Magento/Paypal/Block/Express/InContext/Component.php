<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\InContext;

use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Component
 */
class Component extends Template
{
    const IS_BUTTON_CONTEXT_INDEX = 'is_button_context';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();

        $this->config->setMethod(Config::METHOD_EXPRESS);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->isInContext()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    private function isInContext()
    {
        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return (int) $this->config->getValue('sandbox_flag') ? 'sandbox' : 'production';
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->config->getValue('merchant_id');
    }

    /**
     * @return bool
     */
    public function isButtonContext()
    {
        return (bool) $this->getData(self::IS_BUTTON_CONTEXT_INDEX);
    }
}
