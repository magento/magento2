<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class AbstractTokenRenderer
 * @api
 */
abstract class AbstractTokenRenderer extends Template implements TokenRendererInterface, IconRendererInterface
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
     * @return PaymentTokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    protected function getTokenDetails()
    {
        return $this->tokenDetails;
    }
}
