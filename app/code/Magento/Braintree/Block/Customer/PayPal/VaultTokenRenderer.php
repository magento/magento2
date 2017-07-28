<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Customer\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;

/**
 * Class VaultTokenRenderer
 *
 * @api
 * @since 2.2.0
 */
class VaultTokenRenderer extends AbstractTokenRenderer
{
    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     * @since 2.2.0
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
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIconUrl()
    {
        return $this->config->getPayPalIcon()['url'];
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIconHeight()
    {
        return $this->config->getPayPalIcon()['height'];
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIconWidth()
    {
        return $this->config->getPayPalIcon()['width'];
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     * @since 2.2.0
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::PAYPAL_CODE;
    }

    /**
     * Get email of PayPal payer
     * @return string
     * @since 2.2.0
     */
    public function getPayerEmail()
    {
        return $this->getTokenDetails()['payerEmail'];
    }
}
