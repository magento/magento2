<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Interface ChainFactoryInterface
 *
 * @api
 * @since 100.0.2
 */
interface ChainFactoryInterface
{
    /**
     * Creates chain of pre-processors
     *
     * @param array $arguments
     * @return Chain
     */
    public function create(array $arguments = []);
}
