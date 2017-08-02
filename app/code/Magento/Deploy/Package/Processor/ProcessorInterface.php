<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package\Processor;

use Magento\Deploy\Package\Package;

/**
 * Deploy packages processor interface
 * @since 2.2.0
 */
interface ProcessorInterface
{
    /**
     * Process package
     *
     * Package processors may produce additional (derivative) files or do additional content modifications
     *
     * @param Package $package
     * @param array $options
     * @return bool true on success
     * @since 2.2.0
     */
    public function process(Package $package, array $options);
}
