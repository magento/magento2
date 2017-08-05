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
 * @since 2.1.0
 * @since 100.1.0
 */
interface TokenRendererInterface
{
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     * @since 2.1.0
     * @since 100.1.0
     */
    public function canRender(PaymentTokenInterface $token);

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     * @since 2.1.0
     * @since 100.1.0
     */
    public function render(PaymentTokenInterface $token);

    /**
     * Get payment token
     * @return PaymentTokenInterface|null
     * @since 2.1.3
     * @since 100.2.0
     */
    public function getToken();
}
