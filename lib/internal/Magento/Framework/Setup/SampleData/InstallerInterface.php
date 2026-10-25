<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 *
 * @api
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
