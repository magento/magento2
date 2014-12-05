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

class Compiled extends AbstractEnvironment implements EnvironmentInterface
{
    /**
     * File name with compiled data
     */
    const FILE_NAME = 'global.ser';

    /**
     * Relative path to file with compiled data
     */
    const RELATIVE_FILE_PATH = '/var/di/';

    /**#@+
     * Mode name
     */
    const MODE = 'compiled';
    protected $mode = self::MODE;
    /**#@- */

    /**
     * Global config
     *
     * @var array
     */
    private $globalConfig = [];

    /**
     * @var string
     */
    protected $configPreference = 'Magento\Framework\ObjectManager\Factory\Compiled';

    /**
     * Returns initialized compiled config
     *
     * @return \Magento\Framework\Interception\ObjectManager\Config
     */
    public function getDiConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\Framework\Interception\ObjectManager\Config(
                new \Magento\Framework\ObjectManager\Config\Compiled($this->getConfigData())
            );
        }

        return $this->config;
    }

    /**
     * Returns config data as array
     *
     * @return array
     */
    private function getConfigData()
    {
        if (empty($this->globalConfig)) {
            $this->globalConfig = \unserialize(\file_get_contents(self::getFilePath()));
        }

        return $this->globalConfig;
    }

    /**
     * Returns file path
     *
     * @return string
     */
    public static function getFilePath()
    {
        return BP . self::RELATIVE_FILE_PATH . self::FILE_NAME;
    }

    /**
     * Returns new instance of compiled config loader
     *
     * @return \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     */
    public function getObjectManagerConfigLoader()
    {
        return new \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled($this->getConfigData());
    }
}
