<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    public function configureObjectManager(ConfigInterface $diConfig, &$sharedInstances)
    {
        $originalSharedInstances = $sharedInstances;
        $objectManager = ObjectManager::getInstance();
        $sharedInstances['Magento\Framework\ObjectManager\ConfigLoaderInterface'] = $objectManager
            ->get('Magento\Framework\App\ObjectManager\ConfigLoader');

        $diConfig->setCache(
            $objectManager->get('Magento\Framework\App\ObjectManager\ConfigCache')
        );

        $objectManager->configure(
            $objectManager
                ->get('Magento\Framework\App\ObjectManager\ConfigLoader')
                ->load(Area::AREA_GLOBAL)
        );
        $objectManager->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope('global');
        $diConfig->setInterceptionConfig(
            $objectManager->get('Magento\Framework\Interception\Config\Config')
        );
        /** Reset the shared instances once interception config is set so classes can be intercepted if necessary */
        $sharedInstances = $originalSharedInstances;
        $sharedInstances['Magento\Framework\ObjectManager\ConfigLoaderInterface'] = $objectManager
            ->get('Magento\Framework\App\ObjectManager\ConfigLoader');
    }
}
