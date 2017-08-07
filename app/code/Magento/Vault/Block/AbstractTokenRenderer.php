<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\Customer\IconInterface;

/**
 * Class AbstractTokenRenderer
 * @api
 * @since 2.1.3
 */
abstract class AbstractTokenRenderer extends Template implements TokenRendererInterface, IconInterface
{
    /**
     * @var PaymentTokenInterface|null
     * @since 2.1.3
     */
    private $token;

    /**
     * @var array|null
     * @since 2.1.3
     */
    private $tokenDetails;

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     * @since 2.1.3
     */
    public function render(PaymentTokenInterface $token)
    {
        $this->token = $token;
        $this->tokenDetails = json_decode($this->getToken()->getTokenDetails() ?: '{}', true);
        return $this->toHtml();
    }

    /**
     * @return PaymentTokenInterface|null
     * @since 2.1.3
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array|null
     * @since 2.1.3
     */
    protected function getTokenDetails()
    {
        return $this->tokenDetails;
    }
}
