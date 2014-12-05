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
     * @var \Magento\Framework\Interception\ObjectManager\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Dynamic\Developer';

    /**
     * Returns initialized di config entity
     *
     * @return \Magento\Framework\Interception\ObjectManager\Config
     */
    public function getDiConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\ObjectManager\Config(
                new \Magento\Framework\ObjectManager\Config\Config(
                    $this->envFactory->getRelations(),
                    $this->envFactory->getDefinitions()
                )
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
