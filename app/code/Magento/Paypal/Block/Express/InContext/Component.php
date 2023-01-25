<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\InContext;

use Magento\Framework\App\ObjectManager;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Paypal Express InContext Component.
 *
 * @api
 * @since 100.1.0
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
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        array $data = [],
        ?JsonHelper $jsonHelper = null
    ) {
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();

        $this->config->setMethod(Config::METHOD_EXPRESS);
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    protected function _toHtml()
    {
        if (!$this->isInContext()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Check if is in Context.
     *
     * @return bool
     */
    private function isInContext()
    {
        return (bool)(int) $this->config->getValue('in_context');
    }

    /**
     * Return environment.
     *
     * @return string
     * @since 100.1.0
     */
    public function getEnvironment()
    {
        return (int) $this->config->getValue('sandbox_flag') ? 'sandbox' : 'production';
    }

    /**
     * Return locale.
     *
     * @return string
     * @since 100.1.0
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * Return merchant id.
     *
     * @return string
     * @since 100.1.0
     */
    public function getMerchantId()
    {
        return $this->config->getValue('merchant_id');
    }

    /**
     * Check if button is in context.
     *
     * @return bool
     * @since 100.1.0
     */
    public function isButtonContext()
    {
        return (bool) $this->getData(self::IS_BUTTON_CONTEXT_INDEX);
    }
}
