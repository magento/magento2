<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Report\Data\Config;

use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;

/**
 * Config
 */
abstract class AbstractConfig implements ConfigInterface
{
    /**
     * Modules
     *
     * @var array
     */
    private $modules;

    /**
     * Config construct
     *
     * @param array $modules
     */
    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getDependenciesCount();
}
