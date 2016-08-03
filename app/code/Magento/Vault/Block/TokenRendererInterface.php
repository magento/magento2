<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface TokenRendererInterface
 * @api
 */
interface TokenRendererInterface
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token);

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function render(PaymentTokenInterface $token);

    /**
     * Get payment token
     * @return PaymentTokenInterface|null
     */
    public function getToken();
}
