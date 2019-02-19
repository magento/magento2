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
 * @since 100.0.2
 */
interface ResultInterface
{
    /**
     * Returns result interpretation
     *
     * @return mixed
     */
    public function get();
}
