<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Block\Customer;

use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\TokenRendererInterface;

class CardRenderer extends Template implements TokenRendererInterface
{
    /**
     * @var PaymentTokenInterface|null
     */
    private $token;

    /**
     * @var CcConfigProvider
     */
    private $iconsProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->iconsProvider = $iconsProvider;
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === ConfigProvider::CODE;
    }

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function render(PaymentTokenInterface $token)
    {
        $this->token = $token;
        $result = $this->toHtml();
        $this->token = null;

        return $result;
    }

    /**
     * @return PaymentTokenInterface|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getTokenDetails()
    {
        return json_decode($this->getToken()->getTokenDetails() ?: '{}', true);
    }

    /**
     * @param string $type
     * @return array|null
     */
    public function getIconForType($type)
    {
        if (isset($this->iconsProvider->getIcons()[$type])) {
            return $this->iconsProvider->getIcons()[$type];
        }

        return null;
    }
}
