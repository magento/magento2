<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Interface ChainFactoryInterface
 *
 * @api
 * @since 2.0.0
 */
interface ChainFactoryInterface
{
    /**
     * Creates chain of pre-processors
     *
     * @param array $arguments
     * @return Chain
     * @since 2.0.0
     */
    public function create(array $arguments = []);
}
