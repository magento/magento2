<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\InContext;

use Magento\Paypal\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Button
 */
class Button extends Template
{
    const PAYPAL_BUTTON_ID = 'paypal-express-in-context-checkout-main';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    const CART_BUTTON_ELEMENT_INDEX = 'add_to_cart_selector';

    const CONTEXT_PRODUCT_DETAIL = 'paypal-express-in-context-product-details';

    const CONTEXT_MSRP = 'paypal-express-in-context-msrp';

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
     * @param Config $config
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    private function isInContext()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function shouldRender()
    {
        return $this->isInContext();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getAddToCartSelector()
    {
        return $this->getData(self::CART_BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->config->getExpressCheckoutInContextImageUrl(
            $this->localeResolver->getLocale()
        );
    }
}
