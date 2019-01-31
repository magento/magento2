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
 */
interface SourceFileGeneratorInterface
{
    /**
     * @param Chain $chain
     *
     * @return mixed
     */
    public function generateFileTree(Chain $chain);
}
