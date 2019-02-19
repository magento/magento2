<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Request;

/**
 * Interface BuilderInterface
 * @package Magento\Payment\Gateway\Request
 * @api
 * @since 100.0.2
 */
interface BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject);
}
