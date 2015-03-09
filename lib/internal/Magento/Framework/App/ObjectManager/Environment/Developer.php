<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;

class Developer extends AbstractEnvironment implements EnvironmentInterface
{
    /**#@+
     * Mode name
     */
    const MODE = 'developer';
    protected $mode = self::MODE;
    /**#@- */

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Dynamic\Developer';

    /**
     * Returns initialized di config entity
     *
     * @return ConfigInterface
     */
    public function getDiConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\ObjectManager\Config\Developer(
                $this->envFactory->getRelations(),
                $this->envFactory->getDefinitions()
            );
        }

        return $this->config;
    }

    /**
     * As developer environment does not have config loader, we return null
     *
     * @return null
     */
    public function getObjectManagerConfigLoader()
    {
        return null;
    }

    /**
     * {inheritdoc}
     */
    public function configureObjectManager(ConfigInterface $diConfig)
    {
        $diConfig->setCache(
            ObjectManager::getInstance()->get('Magento\Framework\App\ObjectManager\ConfigCache')
        );

        ObjectManager::getInstance()->configure(
            ObjectManager::getInstance()
                ->get('Magento\Framework\App\ObjectManager\ConfigLoader')
                ->load(Area::AREA_GLOBAL)
        );
        ObjectManager::getInstance()->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope('global');
        $diConfig->setInterceptionConfig(
            ObjectManager::getInstance()->get('Magento\Framework\Interception\Config\Config')
        );
    }
}
