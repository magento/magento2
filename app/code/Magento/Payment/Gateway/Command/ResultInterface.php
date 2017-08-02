<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

/**
 * Interface ResultInterface
 * @package Magento\Payment\Gateway\Command
 * @api
 * @since 2.0.0
 */
interface ResultInterface
{
    /**
     * Returns result interpretation
     *
     * @return mixed
     * @since 2.0.0
     */
    public function get();
}
