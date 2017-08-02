<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\PreProcessor\Chain;

/**
 * Interface SourceFileGenerator
 *
 * @package Magento\Framework\View\Asset
 * @since 2.0.0
 */
interface SourceFileGeneratorInterface
{
    /**
     * @param Chain $chain
     *
     * @return mixed
     * @since 2.0.0
     */
    public function generateFileTree(Chain $chain);
}
