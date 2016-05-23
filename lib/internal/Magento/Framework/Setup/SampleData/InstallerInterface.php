<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 */
interface InstallerInterface
{
    /**
     * Install SampleData module
     *
     * @return void
     */
    public function install();
}
