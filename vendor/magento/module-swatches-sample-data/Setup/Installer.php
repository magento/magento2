<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwatchesSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\SwatchesSampleData\Model\Swatches;
     */
    protected $swatches;

    /**
     * @param \Magento\SwatchesSampleData\Model\Swatches $swatches
     */
    public function __construct(\Magento\SwatchesSampleData\Model\Swatches $swatches)
    {
        $this->swatches = $swatches;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->swatches->install();
    }
}