<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 * @since 2.0.0
 */
interface InstallerInterface
{
    /**
     * Install SampleData module
     *
     * @return void
     * @since 2.0.0
     */
    public function install();
}
