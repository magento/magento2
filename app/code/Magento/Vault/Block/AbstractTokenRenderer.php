<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\Customer\IconInterface;

/**
 * Class AbstractTokenRenderer
 */
abstract class AbstractTokenRenderer extends Template implements TokenRendererInterface, IconInterface
{
    /**
     * @var PaymentTokenInterface|null
     */
    private $token;

    /**
     * @var array|null
     */
    private $tokenDetails;

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function render(PaymentTokenInterface $token)
    {
        $this->token = $token;
        $this->tokenDetails = json_decode($this->getToken()->getTokenDetails() ?: '{}', true);
        return $this->toHtml();
    }

    /**
     * @return PaymentTokenInterface|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array|null
     */
    protected function getTokenDetails()
    {
        return $this->tokenDetails;
    }
}
