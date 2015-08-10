<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\Composer\AbstractComponentUninstaller;
use Magento\Setup\Model\ObjectManagerProvider;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create a component remove command, depending on component type
 */
class ComponentUninstallerFactory
{
    /**
     * Component type to Class name map
     *
     * @var array
     */
    static private $typeMap = [
        JobComponentUninstall::COMPONENT_MODULE => 'Magento\Setup\Model\ModuleUninstaller',
        JobComponentUninstall::COMPONENT_LANGUAGE => '',
        JobComponentUninstall::COMPONENT_THEME => 'Magento\Theme\Model\Theme\ThemeUninstaller'
    ];

    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, ServiceLocatorInterface $serviceLocator)
    {
        $this->objectManagerProvider = $objectManagerProvider;
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create component remove command
     *
     * @param string $type
     * @return AbstractComponentUninstaller
     */
    public function create($type)
    {
        if ($type == JobComponentUninstall::COMPONENT_MODULE) {
            return $this->serviceLocator->get(self::$typeMap[$type]);
        } else {
            return $this->objectManagerProvider->get()->create(self::$typeMap[$type]);
        }
    }
}
