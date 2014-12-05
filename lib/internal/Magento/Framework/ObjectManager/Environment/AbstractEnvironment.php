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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\ObjectManager\Environment;

use Magento\Framework\ObjectManager\EnvironmentFactory;
use Magento\Framework\ObjectManager\EnvironmentInterface;
use Magento\Framework\ObjectManager\Profiler\FactoryDecorator;
use Magento\Framework\ObjectManager\Factory\Compiled as FactoryCompiled;

abstract class AbstractEnvironment implements EnvironmentInterface
{
    /**
     * @var \Magento\Framework\Interception\ObjectManager\Config
     */
    protected $config;

    /**
     * Mode name
     */
    protected $mode = 'developer';

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Dynamic\Developer';

    /**
     * @var \Magento\Framework\ObjectManager\FactoryInterface
     */
    private $factory;

    /**
     * @var EnvironmentFactory
     */
    protected $envFactory;

    /**
     * @param EnvironmentFactory $envFactory
     */
    public function __construct(EnvironmentFactory $envFactory)
    {
        $this->envFactory = $envFactory;
    }

    /**
     * Returns object manager factory
     *
     * @param array $arguments
     * @return FactoryDecorator | FactoryCompiled
     */
    public function getObjectManagerFactory($arguments)
    {
        $factoryClass = $this->getDiConfig()->getPreference($this->configPreference);
        $this->factory = new $factoryClass(
            $this->getDiConfig(),
            null,
            $this->envFactory->getDefinitions(),
            $arguments
        );

        if (isset($arguments['MAGE_PROFILER']) && $arguments['MAGE_PROFILER'] == 2) {
            $this->factory = new FactoryDecorator(
                $this->factory,
                \Magento\Framework\ObjectManager\Profiler\Log::getInstance()
            );
        }

        return $this->factory;
    }

    /**
     * Return name of running mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}
