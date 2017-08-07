<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api\Data;

/**
 * Payment method interface.
 *
 * @api
 * @since 2.1.3
 */
interface PaymentMethodInterface
{
    /**
     * Get code.
     *
     * @return string
     * @since 2.1.3
     */
    public function getCode();

    /**
     * Get title.
     *
     * @return string
     * @since 2.1.3
     */
    public function getTitle();

    /**
     * Get store id.
     *
     * @return int
     * @since 2.1.3
     */
    public function getStoreId();

    /**
     * Get is active.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.1.3
     */
    public function getIsActive();
}
