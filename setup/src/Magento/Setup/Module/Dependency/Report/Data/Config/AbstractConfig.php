<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Data\Config;

use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;

/**
 * Config
 * @since 2.0.0
 */
abstract class AbstractConfig implements ConfigInterface
{
    /**
     * Modules
     *
     * @var array
     * @since 2.0.0
     */
    private $modules;

    /**
     * Config construct
     *
     * @param array $modules
     * @since 2.0.0
     */
    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    abstract public function getDependenciesCount();
}
