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
 * @since 100.1.3
 */
class VaultTokenRenderer extends AbstractTokenRenderer
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Initialize dependencies.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     * @since 100.1.3
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
     * @since 100.1.3
     */
    public function getIconUrl()
    {
        return $this->config->getPayPalIcon()['url'];
    }

    /**
     * @inheritdoc
     * @since 100.1.3
     */
    public function getIconHeight()
    {
        return $this->config->getPayPalIcon()['height'];
    }

    /**
     * @inheritdoc
     * @since 100.1.3
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
     * @since 100.1.3
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::PAYPAL_CODE;
    }

    /**
     * Get email of PayPal payer
     * @return string
     * @since 100.1.3
     */
    public function getPayerEmail()
    {
        return $this->getTokenDetails()['payerEmail'];
    }
}
