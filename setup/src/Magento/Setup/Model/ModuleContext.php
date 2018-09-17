<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Context of a module being installed/updated: version, user data, etc.
 */
class ModuleContext implements ModuleContextInterface
{
    /**
     * Current version of a module
     *
     * @var string
     */
    private $version;

    /**
     * Init
     *
     * @param string $version Current version of a module
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }
}
