<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface TokenRendererInterface
 * @api
 * @since 100.1.0
 */
interface TokenRendererInterface
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     * @since 100.1.0
     */
    public function canRender(PaymentTokenInterface $token);

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     * @since 100.1.0
     */
    public function render(PaymentTokenInterface $token);

    /**
     * Get payment token
     * @return PaymentTokenInterface|null
     * @since 100.2.0
     */
    public function getToken();
}
