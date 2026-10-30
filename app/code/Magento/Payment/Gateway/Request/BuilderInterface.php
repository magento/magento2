<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
