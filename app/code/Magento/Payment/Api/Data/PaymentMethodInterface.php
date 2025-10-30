<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Api\Data;

/**
 * Payment method interface.
 *
 * @api
 * @since 100.1.3
 */
interface PaymentMethodInterface
{
    /**
     * Get code.
     *
     * @return string
     * @since 100.1.3
     */
    public function getCode();

    /**
     * Get title.
     *
     * @return string
     * @since 100.1.3
     */
    public function getTitle();

    /**
     * Get store id.
     *
     * @return int
     * @since 100.1.3
     */
    public function getStoreId();

    /**
     * Get is active.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 100.1.3
     */
    public function getIsActive();
}
