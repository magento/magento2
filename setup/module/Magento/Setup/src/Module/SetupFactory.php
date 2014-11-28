<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Module;

use Magento\Framework\App\Resource;
use Zend\ServiceManager\ServiceLocatorInterface;
use Magento\Setup\Model\LoggerInterface;

class SetupFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param ResourceFactory $resourceFactory
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        \Magento\Setup\Module\ResourceFactory $resourceFactory
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Creates Setup
     *
     * @return Setup
     */
    public function createSetup()
    {
        return new Setup($this->getResource());
    }

    /**
     * Creates SetupModule
     *
     * @param LoggerInterface $log
     * @param string $moduleName
     * @return SetupModule
     */
    public function createSetupModule(LoggerInterface $log, $moduleName)
    {
        return new SetupModule(
            $log,
            $this->serviceLocator->get('Magento\Framework\Module\ModuleList'),
            $this->serviceLocator->get('Magento\Setup\Module\Setup\FileResolver'),
            $moduleName,
            $this->getResource()
        );
    }

    private function getResource()
    {
        $deploymentConfig = new \Magento\Framework\App\DeploymentConfig(
            $this->serviceLocator->get('Magento\Framework\App\DeploymentConfig\Reader'),
            []
        );
        return $this->resourceFactory->create($deploymentConfig);
    }
}
