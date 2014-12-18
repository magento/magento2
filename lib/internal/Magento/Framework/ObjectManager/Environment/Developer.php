<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\ObjectManager\Environment;

use Magento\Framework\ObjectManager\EnvironmentInterface;

class Developer extends AbstractEnvironment implements EnvironmentInterface
{
    /**#@+
     * Mode name
     */
    const MODE = 'developer';
    protected $mode = self::MODE;
    /**#@- */

    /**
     * @var \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    protected $config;

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Dynamic\Developer';

    /**
     * Returns initialized di config entity
     *
     * @return \Magento\Framework\Interception\ObjectManager\ConfigInterface
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
}
