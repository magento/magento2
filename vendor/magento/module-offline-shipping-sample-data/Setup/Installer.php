<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShippingSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\OfflineShippingSampleData\Model\Tablerate
     */
    private $tablerate;

    /**
     * @param \Magento\OfflineShippingSampleData\Model\Tablerate $tablerate
     */
    public function __construct(\Magento\OfflineShippingSampleData\Model\Tablerate $tablerate) {
        $this->tablerate = $tablerate;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->tablerate->install(['Magento_OfflineShippingSampleData::fixtures/tablerate.csv']);
    }
}