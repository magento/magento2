<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Math\Random;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Setup\TextConfigOption;
use Magento\Framework\Setup\MultiSelectConfigOption;
use Magento\Framework\Module\ModuleList\Loader;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';

    /**
     * Input key for encryption key
     */
    const INPUT_KEY_CRYPT_KEY = 'key';

    /**
     * Path to modules in the deployment config
     */
    const CONFIG_PATH_MODULES = 'modules';

    /**
     * @var array
     */
    private $moduleList;

    /**
     * @var Random
     */
    private $random;

    /**
     * Constructor
     *
     * @param Random $random
     * @param Loader $moduleLoader
     */
    public function __construct(Random $random, Loader $moduleLoader)
    {
        $this->random = $random;
        $this->moduleList = $moduleLoader->load();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(self::INPUT_KEY_CRYPT_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'encryption key'
            ),
            new MultiSelectConfigOption(
                self::CONFIG_PATH_MODULES,
                MultiSelectConfigOption::FRONTEND_WIZARD_MULTISELECT,
                $this->moduleList,
                'modules list',
                $this->moduleList
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $config = [];
        $config['install']['date'] = date('r');
        if (isset($data[self::INPUT_KEY_CRYPT_KEY]) && !$data[self::INPUT_KEY_CRYPT_KEY]) {
            throw new \InvalidArgumentException('Invalid encryption key.');
        }
        if (!isset($data[self::INPUT_KEY_CRYPT_KEY])) {
            $config['crypt']['key'] = md5($this->random->getRandomString(10));
        } else {
            $config['crypt']['key'] = $data[self::INPUT_KEY_CRYPT_KEY];
        }

        if (isset($this->moduleList)) {
            foreach ($this->moduleList as $key) {
                $config['modules'][$key] = 1;
            }
        }

        return $config;
    }
}
