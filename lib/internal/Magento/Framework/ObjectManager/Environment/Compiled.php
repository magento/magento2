<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
