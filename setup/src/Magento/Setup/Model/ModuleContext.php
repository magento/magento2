<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Context of a module being installed/updated: version, user data, etc.
 *
 * @api
 * @since 2.0.0
 */
class ModuleContext implements ModuleContextInterface
{
    /**
     * Current version of a module
     *
     * @var string
     * @since 2.0.0
     */
    private $version;

    /**
     * Init
     *
     * @param string $version Current version of a module
     * @since 2.0.0
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getVersion()
    {
        return $this->version;
    }
}
