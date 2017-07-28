<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\ObjectManager\Environment;

use Magento\Framework\App\EnvironmentInterface;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;

/**
 * Class \Magento\Framework\App\ObjectManager\Environment\Developer
 *
 * @since 2.0.0
 */
class Developer extends AbstractEnvironment implements EnvironmentInterface
{
    /**#@+
     * Mode name
     */
    const MODE = 'developer';
    /**#@- */

    /**
     * @var string
     */
    protected $mode = self::MODE;

    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $configPreference = \Magento\Framework\ObjectManager\Factory\Dynamic\Developer::class;

    /**
     * Returns initialized di config entity
     *
     * @return ConfigInterface
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getObjectManagerConfigLoader()
    {
        return null;
    }

    /**
     * {inheritdoc}
     * @since 2.0.0
     */
    public function configureObjectManager(ConfigInterface $diConfig, &$sharedInstances)
    {
        $originalSharedInstances = $sharedInstances;
        $objectManager = ObjectManager::getInstance();
        $sharedInstances[\Magento\Framework\ObjectManager\ConfigLoaderInterface::class] = $objectManager
            ->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class);

        $diConfig->setCache(
            $objectManager->get(\Magento\Framework\App\ObjectManager\ConfigCache::class)
        );

        $objectManager->configure(
            $objectManager
                ->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class)
                ->load(Area::AREA_GLOBAL)
        );
        $objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope('global');
        $diConfig->setInterceptionConfig(
            $objectManager->get(\Magento\Framework\Interception\Config\Config::class)
        );
        /** Reset the shared instances once interception config is set so classes can be intercepted if necessary */
        $sharedInstances = $originalSharedInstances;
        $sharedInstances[\Magento\Framework\ObjectManager\ConfigLoaderInterface::class] = $objectManager
            ->get(\Magento\Framework\App\ObjectManager\ConfigLoader::class);
    }
}
