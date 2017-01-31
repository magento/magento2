<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Api\Data;

/**
 * Payment method interface.
 */
interface PaymentMethodInterface
{
    /**
     * Get code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get store id.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get is active.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();
}
