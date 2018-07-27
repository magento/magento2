<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

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
