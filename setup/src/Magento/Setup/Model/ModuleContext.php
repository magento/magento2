<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Context of a module being installed/updated: version, user data, etc.
 *
 * @api
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
