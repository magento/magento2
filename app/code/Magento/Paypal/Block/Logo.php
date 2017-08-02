<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PayPal online logo with additional options
 */
namespace Magento\Paypal\Block;

/**
 * @api
 * @since 2.0.0
 */
class Logo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Paypal\Model\Config
     * @since 2.0.0
     */
    protected $_paypalConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\Config $paypalConfig
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_paypalConfig = $paypalConfig;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * Return URL for Paypal Landing page
     *
     * @return string
     * @since 2.0.0
     */
    public function getAboutPaypalPageUrl()
    {
        return $this->_getConfig()->getPaymentMarkWhatIsPaypalUrl($this->_localeResolver);
    }

    /**
     * Getter for paypal config
     *
     * @return \Magento\Paypal\Model\Config
     * @since 2.0.0
     */
    protected function _getConfig()
    {
        return $this->_paypalConfig;
    }

    /**
     * Disable block output if logo turned off
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        $type = $this->getLogoType();
        // assigned in layout etc.
        $logoUrl = $this->_getConfig()->getAdditionalOptionsLogoUrl($this->_localeResolver->getLocale(), $type);
        if (!$logoUrl) {
            return '';
        }
        $this->setLogoImageUrl($logoUrl);
        return parent::_toHtml();
    }
}
